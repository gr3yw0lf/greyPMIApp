#!/usr/bin/perl

use warnings;
use strict;

use LWP::Simple;
use Data::Dumper;
use JSON;
# loads auth keys and destinations
# NOTE: Assumes that the module is in the standard lib path of perl
use GreyPMIApp::Secure;
use Astro::Coord::ECI;
use Astro::Coord::ECI::Moon;
use Astro::Coord::ECI::Utils qw{deg2rad};
use Astro::MoonPhase;
use POSIX qw(strftime);

my $moonDetails= ();
my $connectionDetails = new GreyPMIApp::Secure;

$moonDetails->{'dataType'} = 'Moon';
$moonDetails->{'debug'} = 'false';
$moonDetails->{'auth'} = $connectionDetails->getAuth();

&getMoonDetails(\$moonDetails->{'keys'});
&getRiseSetTimes(\$moonDetails->{'keys'});

my $json = JSON->new;
my $jsonString = $json->encode( $moonDetails );

print $jsonString;

print "\n Sending .... \n";

my @locations = $connectionDetails->getUrls();
foreach my $url (@locations) {

	my $req = HTTP::Request->new(POST => $url);
	$req->content_type('application/x-www-form-urlencoded');
	$req->content('data='. $jsonString );

	#my $req = HTTP::Request->new( 'POST', $url );
	#$req->header( 'Content-Type' => 'application/json' );
	#$req->content( "data:" . $jsonString );

	 # from: http://stackoverflow.com/questions/4199266/how-can-i-make-a-json-post-request-with-lwp
	#	Then you can execute the request with LWP:
	my $lwp = LWP::UserAgent->new;
	my $result = $lwp->request( $req );

	if ($result->is_success) {
	    print "#url = $url: Success:" . $result->content . "\n";
	} else {
	    print "#url = $url: Failed:" . $result->status_line, "\n";
	}
}


#######################################################################
# Functions
#

# drawMoonDetails($imgPtr,$x,$y)
sub getMoonDetails($) {
	my ($dataPtr) = @_;


	my @phaseData = phase();
	my $count = 0;
	foreach my $item (qw/phase illum age distance angle sunDistance sunAngle/) {
		$$dataPtr->{$item} = $phaseData[$count++];
	}

	#my  @phases = phasehunt();
	#$count = 0;
	#foreach my $item (qw/NewMoon FirstQuarter FullMoon LastQuarter NewMoon2/) {
	#	$$dataPtr->{$item} = strftime("%a %b %e %H:%M:%S", localtime($phases[$count]));
	#	$count++;
	#}

	my $moonIcon;

	# moon age is from last new moon
	# whilst age < (29.530/2.0)
	# 0%   = 0 = 0%-22%
	# 25%  = 1 = 22%-47%
	# 50%  = 2 = 47%-53%
	# 75%  = 3 = 53%-90%
	# 100% = 4 = 90%-100%
	# whilst age > (29.530/2.0)
	# 100% = 4 = 100%-90%
	# 75%  = 5 = 90%-53%
	# 50%  = 6 = 53%-47%
	# 25%  = 7 = 47%-22%
	# 0%   = 0 = 22%-0%

	if ($$dataPtr->{'age'} < (29.530/2.0)) {
		if ($$dataPtr->{'illum'} < 0.22) {
			$moonIcon = 0;
		} elsif ($$dataPtr->{'illum'} < 0.47) {	
			$moonIcon = 1;
		} elsif ($$dataPtr->{'illum'} < 0.53) {
			$moonIcon = 2;
		} elsif ($$dataPtr->{'illum'} < 0.90) {
			$moonIcon = 3;
		} else {
			$moonIcon = 4;
		}
	} else { # $MoonAge >(29.530/2.0))
		if ($$dataPtr->{'illum'} < 0.22) {
			$moonIcon = 0;
		} elsif ($$dataPtr->{'illum'} < 0.47) {
			$moonIcon = 7;
		} elsif ($$dataPtr->{'illum'} < 0.53) {
			$moonIcon = 6;
		} elsif ($$dataPtr->{'illum'} < 0.90) {
			$moonIcon = 5;
		} else {
			$moonIcon = 4;
		}
	}

	$$dataPtr->{'iconNumber'} = $moonIcon;

	my ($phase, @times) = phaselist(time-(28*24*60*60), time+(28*24*60*60));
	#my @nameKeys = ("newMoon", "firstQuarter", "fullMoon", "lastQuarter");
	
	my @order = ();
	# need to add the phase to each item on time in times
	foreach my $time (@times) {
		push(@order,"$time=$phase");
		$phase = ($phase + 1) % 4;
	}
	$$dataPtr->{'phaseOrder'} = join(",",@order);

}

sub getRiseSetTimes() {
	my ($dataPtr) = @_;
	# home = 41°48′8″N 80°3′33″W=
	#   41.002222 , -80.059167

	my $lat = deg2rad (41.002222);    # Radians
	my $long = deg2rad (-80.059167);  # Radians
	my $alt = 1150 / 1000;        # Kilometers
	my $moon = Astro::Coord::ECI::Moon->new ();
	my $sta = Astro::Coord::ECI->
		universal (time ())->
		geodetic ($lat, $long, $alt);
	my ($time, $rise) = $sta->next_elevation ($moon);

	# go three days back, and 3 days forward
	my @almanac = $moon->almanac_hash($sta, time - 3*24*60*60, time + 3*24*60*60);

	my @moonRise;
	my @moonSet;

	foreach my $almanacItem (@almanac) {
		if ($almanacItem->{'almanac'}->{'event'} eq "horizon") {
			if ($almanacItem->{'almanac'}->{'description'} eq 'Moon set') {
				push(@moonSet, $almanacItem->{'time'});
			}
			if ($almanacItem->{'almanac'}->{'description'} eq 'Moon rise') {
				push(@moonRise, $almanacItem->{'time'});
			}
		}
	}

	my @closestSetTimeKeys = &findClosest(3,time,12*60*60,1,@moonSet);
	my @closestRiseTimeKeys = &findClosest(3,time,12*60*60,1,@moonRise);
	
	$$dataPtr->{'riseSet'} = join(",",@closestRiseTimeKeys,@closestSetTimeKeys);
}

sub findClosest() {
    my ($amount,$val,$difference,$valPos,@keys) = @_;
    my @sortedKeys = sort {
        $a <=> $b
    } @keys;

    my @buffer = (0) x $amount;
    my $finished = 0;
    my $count = 0;
    while (!$finished) {
        for my $i (1 .. $amount-1) {
            print " >>>> i = $i\n";
            $buffer[$i-1] = $buffer[$i];
        }
        $buffer[$amount-1] = $sortedKeys[$count];
        if (abs(($buffer[$valPos]) - $val) < $difference ) {
            $finished = 1;
        }
        print ">>> buf = " . join(",",@buffer) . "\n";
        $count++;
        if ($count>scalar @sortedKeys) {
            die;
        }
    }
    return @buffer;
}

