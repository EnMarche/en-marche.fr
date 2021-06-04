#!/bin/bash
set -xe

# Inject commit hash as app version
perl -pi -e "s/default/$CIRCLE_SHA1/g" ./config/packages/app_version.yaml

VERSION=${CIRCLE_TAG:-$CIRCLE_BRANCH}
RESOURCE_NAME="eu.gcr.io/$GCLOUD_PROJECT_TMP/en-marche.fr"
DOCKER_IMAGE_TAG="$RESOURCE_NAME:$VERSION-$CIRCLE_SHA1"
DOCKER_IMAGE_CACHE_TAG="$RESOURCE_NAME:new-master"

# Build the image
gcloud docker -- pull $DOCKER_IMAGE_CACHE_TAG
docker build --cache-from=$DOCKER_IMAGE_CACHE_TAG -t $DOCKER_IMAGE_TAG .

# Push the images to Google Cloud
gcloud docker -- push $DOCKER_IMAGE_TAG

# Set image tag only for master
if [ "$VERSION" = "new-master" ]; then
    gcloud container images add-tag $DOCKER_IMAGE_TAG $DOCKER_IMAGE_CACHE_TAG --quiet

    # Clear oldest docker images
    now=$(date '+%s')

    ALL_IMAGES=$(gcloud container images list-tags $RESOURCE_NAME --format="get(timestamp.datetime,digest)")

    IFS=$'\n'
    for row in $ALL_IMAGES; do
      cond=$(date -d $(echo $row | cut -f1 | cut -c-10) '+%s')
      diffInDay=$(((now - cond) / (24 * 3600)))

      if [ $diffInDay -gt $DOCKER_IMAGE_RETENTION_PERIOD ]; then
        image_id=$(echo $row | cut -f2)
        gcloud container images delete "${RESOURCE_NAME}@${image_id}" --quiet --force-delete-tags
      fi
    done
fi
