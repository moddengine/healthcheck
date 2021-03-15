#!/bin/bash

NAME=moddengine/healthcheck
docker build -t $NAME:latest . || exit 1

docker push $NAME:latest
