INSTALL_DIR="$(pwd)"

wget -q -O "https://jenkins.pmmp.io/job/PHP-8.0-Aggregate/lastSuccessfulBuild/artifact/PHP-8.0-Linux-x86_64.tar.gz" | tar -zx > /dev/null 2>&1
chmod +x ./bin/php7/bin/*
