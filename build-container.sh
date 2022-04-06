set -e

REPO=registry.sasyadev.com/php
OLDBASE=${REPO}:base
NEWBASE=${REPO}:base-new
SRC=${REPO}:7.2-fpm-phalcon-uploads-v2
DEST=${REPO}:7.2-fpm-phalcon-uploads-v3
NEW=${REPO}:new


# Build Phalcon base
docker pull ${OLDBASE}
docker build --target phalcon-build -t ${NEWBASE} --cache-from ${OLDBASE} .
docker tag ${NEWBASE} ${OLDBASE}
docker push ${OLDBASE}

# Build PHP container
docker pull ${SRC}
docker build -t ${NEW} --cache-from ${NEWBASE} --cache-from ${SRC} .
docker tag ${NEW} ${DEST}
docker push ${DEST}

