#!/usr/bin/env bash

FILE=`ls -1 | egrep 'cbackup-[0-9]+\.[0-9]+\.[0-9]\.tar\.gz'`
VERS=`ls -1 | egrep 'cbackup-[0-9]+\.[0-9]+\.[0-9]\.tar\.gz' | sed -e s/[^0-9\.]//g | sed 's/\.\.$//'`
RHEL=`cat /etc/redhat-release | awk -Frelease {'print $2'} | awk {'print $1'} | awk -F. {'print $1'}`

if [ "$(id -u)" = "0" ]; then
    echo -e '\033[0;31m[ERROR]\033[0m Do not run this script as root'
    exit 1
fi

if [ ! -f "$FILE" ]; then
    echo -e '\033[0;31m[ERROR]\033[0m Archive not found'
    exit 1
fi

if [ -d ~/rpmbuild ]; then
  rm -rf ~/rpmbuild
fi

# mkdir -p ~/rpmbuild/{BUILD,RPMS,SOURCES,SPECS,SRPMS}
rpmdev-setuptree

# repack archive
mkdir ~/rpmbuild/SOURCES/cbackup-${VERS}
tar xf ~/${FILE} -C ~/rpmbuild/SOURCES/cbackup-${VERS}
rm -f ${FILE}
cp ~/rpmbuild/SOURCES/cbackup-${VERS}/install/build/centos/cbackup.el${RHEL}.spec ~/rpmbuild/SPECS/
tar czf ~/rpmbuild/SOURCES/${FILE} -C ~/rpmbuild/SOURCES/ . > /dev/null 2>&1
rm -rf ~/rpmbuild/SOURCES/cbackup-${VERS}

# -- Build and install
echo ""
echo "-- Preparation complete, now build package:"
echo "rpmbuild -bb ~/rpmbuild/SPECS/cbackup.el${RHEL}.spec"
echo ""
echo "-- To install:"
echo "sudo rpm -ivh cbackup_${VERS}-1.el${RHEL}.centos.noarch.rpm"
echo "sudo rpm -e cbackup_${VERS}-1.el${RHEL}.centos.noarch"
echo ""