FROM debian:buster-20191118-slim as base
MAINTAINER Sean Morris <sean@seanmorr.is>

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		apt-transport-https \
		ca-certificates \
		gnupg \
		lsb-release \
		software-properties-common \
		wget \
	&& apt-get clean

RUN wget -qO /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
	&& sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" \
		 > /etc/apt/sources.list.d/sury-php.list'

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		libargon2-0   \
		libsodium23   \
		libssl1.1     \
		libyaml-dev   \
		php7.3           \
		php7.3-cli       \
		php7.3-common    \
		php7.3-json      \
		php7.3-opcache   \
		php7.3-readline  \
		php7.3-redis     \
		php7.3-yaml      \
	&& apt-get clean

RUN apt-get update \
	&& apt-get install -y --no-install-recommends apache2 libapache2-mod-php7.3 \
	&& apt-get clean \
	&& rm -rfv /var/www/html \
	&& ln -s /app/public /var/www/html
	
COPY ./infra/apache/000-default.conf /etc/apache2/sites-enabled

RUN a2dismod mpm_event \
	&& a2enmod rewrite php7.3

RUN ln -sf /proc/self/fd/1 /var/log/apache2/access.log \
	&& ln -sf /proc/self/fd/1 /var/log/apache2/error.log

WORKDIR /app/public

RUN apachectl configtest

ENTRYPOINT ["apachectl", "-D", "FOREGROUND"]

FROM base as dev

RUN apt-get update \
	&& apt-get install -y --no-install-recommends php7.3-xdebug \
	&& apt-get clean

COPY ./infra/xdebug/30-xdebug-cli.ini /etc/php/7.3/cli/conf.d/30-xdebug-cli.ini
COPY ./infra/xdebug/30-xdebug-apache.ini /etc/php/7.3/apache2/conf.d/30-xdebug-apache.ini

FROM base as prod

COPY ./ /app
