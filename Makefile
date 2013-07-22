ROOT = /home/web/moon/htdocs
APP_DIR = greyPMIApp

include ~/programming/makefiles/web.include
#
# install the files on webhosting
include ~/programming/makefiles/hosting.include

# install the perl module
install-perl: /etc/perl/GreyPMIApp senders/GreyPMIApp/Secure.pm
	cp senders/GreyPMIApp/Secure.pm /etc/perl/GreyPMIApp/Secure.pm
	cp senders/cron.d.greyPMIAppSend /etc/cron.d/greyPMIAppSend
	cp senders/*.pl /home/web/scripts/

/etc/perl/GreyPMIApp:
	mkdir /etc/perl/GreyPMIApp

