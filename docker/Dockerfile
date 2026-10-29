ARG SOLR_VERSION=10.0.0

FROM alpine:3.22 AS builder
ARG SOLR_VERSION
RUN apk add --no-cache --upgrade bash curl
RUN adduser --disabled-password user
USER user
WORKDIR /home/user
COPY --chown=user bin/generate-solr-config.sh solr/bin/
COPY --chown=user src/lib/Resources/config/solr solr/src/lib/Resources/config/solr
# For Solr 9+ the generator also writes solr.xml two levels above the destination, keep it inside config/
RUN ./solr/bin/generate-solr-config.sh --destination-dir=config/template/conf --solr-version="${SOLR_VERSION}"

FROM solr:${SOLR_VERSION}
LABEL org.opencontainers.image.source=https://github.com/ibexa/solr/blob/6.0/docker/Dockerfile
LABEL org.opencontainers.image.description="Configured Ibexa Solr Bundle image, created by 'Build and publish Solr Docker image' GHA workflow"
LABEL org.opencontainers.image.licenses=GPL-2.0-only
USER root
RUN rm -rf server/solr/configsets/_default/conf/*
USER solr
COPY --from=builder /home/user/config/template/conf server/solr/configsets/_default/conf
CMD ["solr-precreate", "collection1"]
