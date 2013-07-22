#!/usr/bin/perl

use warnings;
use strict;

use LWP::Simple;
use Data::Dumper;
use JSON;
# loads auth keys and destinations
# NOTE: Assumes that the module is in the standard lib path of perl
use GreyPMIApp::Secure;

my $moonDetails= ();
my $connectionDetails = new GreyPMIApp::Secure;

$moonDetails->{'dataType'} = 'Moon';
$moonDetails->{'debug'} = 'false';
$moonDetails->{'auth'} = $connectionDetails->getAuth();

&getMoonDetails(\$moonDetails->{'keys'});

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

	use Astro::MoonPhase;
	use POSIX qw(strftime);

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

	my ($phase, @times) = phaselist(time-(30*24*60*60), time+(30*24*60*60));
	#my ($phase, @times) = phaselist(time-(1*24*60*60), time+(30*24*60*60));
	my @nameKeys = ("newMoon", "firstQuarter", "fullMoon", "lastQuarter");
	$count=0;
	my @order;
	foreach my $time (@times) {
		my $prev = $time-time;
		my $key = $nameKeys[$phase];
		if ($prev<0) {
			$key = $key."Prev";
		}
		push(@order,$key);
		$$dataPtr->{$key} = strftime('%a %b %e %H:%M:%S', localtime($time));
		$phase = ($phase + 1) % 4;
		$count++;
	}
	$$dataPtr->{'phaseOrder'} = join(",",@order);

}

