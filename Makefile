#!make

TARGET ?=dev

.PHONY: it clean build images start start-fg restart restart-fg stop stop-all tag run test env

-include .env
-include .env.${TARGET}

PROJECT =route-test
REPO    =seanmorris

BRANCH ?=`git rev-parse --abbrev-ref HEAD 2>/dev/null`
DESC   ?=`git describe --tags 2>/dev/null || git rev-parse --short HEAD 2>/dev/null || echo initial`

TAG       ?=${BRANCH}-${DESC}-${TARGET}
IMAGE     ?=
DHOST_IP  ?=`docker network inspect bridge --format="{{ (index .IPAM.Config 0).Gateway}}"`

XDEBUG_CONFIG_REMOTE_HOST?=${DHOST_IP}

ifeq ($(TARGET),dev)
	XDEBUG_ENV=XDEBUG_CONFIG="`\
		cat .env.dev \
		| env DHOST_IP=$$(${subst `, , ${XDEBUG_CONFIG_REMOTE_HOST}}) envsubst \
		| grep -v '^\#' \
		| grep ^XDEBUG_CONFIG_ \
		| while read VAR; do echo $$VAR | \
		{ \
			IFS='\=' read -r NAME VALUE; \
			echo -n ' '; \
			echo -n $$NAME | sed -e 's/^XDEBUG_CONFIG_\(.\+\)/\L\1/'; \
			echo -n =$$VALUE;\
		} \
		; done | cut -c 2-`"
else
	XDEBUG_ENV=
endif

ENV=$$(grep -vhs '^\#' .env .env.${TARGET} | xargs) \
	TAG=${TAG} REPO=${REPO} TARGET=${TARGET} DHOST_IP=${DHOST_IP} \
	${XDEBUG_ENV}

DCOMPOSE ?=export ${ENV} \
	&& docker-compose \
	-p ${PROJECT} \
	-f infra/compose/${TARGET}.yml
	

it:
	echo Building ${PROJECT} ${TAG}
	@ docker run --rm \
		-v $$PWD:/app \
		debian:buster-20191118-slim bash -c "\
			cp -n .env.sample .env 2>/dev/null || true\
			cp -n .env.dev.sample .env.dev 2>/dev/null || true \
		" \
	@ docker run --rm \
		-v $$PWD:/app \
		-v $${COMPOSER_HOME:-$$HOME/.composer}:/tmp \
		composer install
	@ ${DCOMPOSE} build
	@ ${DCOMPOSE} up --no-start
	@ ${DCOMPOSE} images -q | while read IMAGE_HASH; do \
		docker image inspect --format="{{index .RepoTags 0}}" $$IMAGE_HASH \
		| grep "^${REPO}" \
		| while read IMAGE_NAME; do \
			IMAGE_PREFIX=`echo "$$IMAGE_NAME" | sed -e "s/\:.*\$$//"`; \
			docker tag "$$IMAGE_HASH" "$$IMAGE_PREFIX":latest-${TARGET}; \
			echo "$$IMAGE_PREFIX":latest-${TARGET}; \
		done; \
	done;
	@ ${DCOMPOSE} images

fill:
	@ ${DCOMPOSE} run --rm --entrypoint bash backend -c \
		"cd /app/test && php fillDb.php"

composer-update:
	@ docker run --rm \
		-v $$PWD:/app \
		-v $${COMPOSER_HOME:-$$HOME/.composer}:/tmp \
		composer update

restart:
	@ make stop
	@ make start

restart-fg:
	@ make stop
	@ make start-fg

start:
	@ ${DCOMPOSE} up -d

start-fg:
	@ ${DCOMPOSE} up

stop:
	@ ${DCOMPOSE} down

stop-all:
	@ ${DCOMPOSE} down --remove-orphans

_push-images:
	${DCOMPOSE} push

push-images:
	make _push-images
	make _push-images TAG=latest-${TARGET}

pull-images:
	${DCOMPOSE} pull
	