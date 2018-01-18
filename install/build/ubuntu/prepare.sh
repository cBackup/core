#!/usr/bin/env bash

FILE=`ls -1 | egrep 'cbackup-[0-9]+\.[0-9]+\.[0-9]\.tar\.gz'`
VERS=`ls -1 | egrep 'cbackup-[0-9]+\.[0-9]+\.[0-9]\.tar\.gz' | sed -e s/[^0-9\.]//g | sed 's/\.\.$//'`

if [ "$(id -u)" = "0" ]; then
    echo -e '\033[0;31m[ERROR]\033[0m Do not run this script as root'
    exit 1
fi

if [ ! -f "$FILE" ]; then
    echo -e '\033[0;31m[ERROR]\033[0m Archive not found'
    exit 1
fi

if [ -d ~/cbackup ]; then
  rm -rf ~/cbackup
fi

mkdir -p ~/cbackup/etc/logrotate.d
mkdir -p ~/cbackup/etc/rsyslog.d
mkdir -p ~/cbackup/etc/sudoers.d
mkdir -p ~/cbackup/lib/systemd/system
mkdir -p ~/cbackup/opt/cbackup
mkdir -p ~/cbackup/var/log/cbackup

# extract archive
tar xzf ${FILE} -C ~/cbackup/opt/cbackup

# Shuffle stuff
mv ~/cbackup/opt/cbackup/install/build/ubuntu/DEBIAN ~/cbackup/
cp -r ~/cbackup/opt/cbackup/install/system/ubuntu/* ~/cbackup/
cp -r ~/cbackup/opt/cbackup/install/system/centos/systemctl/usr/lib/systemd ~/cbackup/lib
mv ~/cbackup/opt/cbackup/install/system/centos/systemctl/etc/logrotate.d ~/cbackup/etc/
mv ~/cbackup/opt/cbackup/install/system/centos/systemctl/etc/sudoers.d ~/cbackup/etc/

# Cleanup
rm -rf ~/cbackup/opt/cbackup/install/build
rm -rf ~/cbackup/opt/cbackup/install/system
rm -f ${FILE}

# -- Build and install
echo ""
echo "-- Preparation complete, now build package:"
echo "fakeroot dpkg-deb --build cbackup"
echo "mv cbackup.deb cbackup_${VERS}-1_all.deb"
echo ""
echo "-- To install:"
echo "sudo dpkg -i cbackup_${VERS}-1_all.deb"
echo "sudo dpkg -r cbackup"
echo ""