FROM alpine:3.18.5 as builder
RUN apk add --no-cache --upgrade bash curl
RUN adduser --disabled-password user
USER user
WORKDIR /home/user
COPY --chown=user bin/generate-solr-config.sh solr/bin/
COPY --chown=user src/lib/Resources/config/solr solr/src/lib/Resources/config/solr
RUN ./solr/bin/generate-solr-config.sh --destination-dir=config --solr-version=8.6.3

FROM solr:8.6.3
USER root
RUN rm -rf server/solr/configsets/_default/conf/*
USER solr
COPY --from=builder /home/user/config server/solr/configsets/_default/conf
CMD ["solr-precreate", "collection1"]
