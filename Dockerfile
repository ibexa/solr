# syntax=docker/dockerfile:1
FROM solr:8.11.2

USER root
ENV IBEXA_TEMPLATE_DIR=/opt/solr/server/ibexa/template

RUN mkdir -p ${IBEXA_TEMPLATE_DIR}
COPY src/lib/Resources/config/solr/ ${IBEXA_TEMPLATE_DIR}/

RUN set -eux; \
    mkdir -p /opt/solr/server/ibexa; \
    for f in solrconfig.xml stopwords.txt synonyms.txt; do \
        cp /opt/solr/server/solr/configsets/_default/conf/$f ${IBEXA_TEMPLATE_DIR}/; \
    done; \
    cp /opt/solr/server/solr/solr.xml /opt/solr/server/ibexa/

RUN sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema"/,/<\/updateRequestProcessorChain>/d' \
        ${IBEXA_TEMPLATE_DIR}/solrconfig.xml \
    && sed -i '/<autoSoftCommit>/,/<\/autoSoftCommit>/c\<autoSoftCommit>\n  <maxTime>${solr.autoSoftCommit.maxTime:20}</maxTime>\n</autoSoftCommit>' \
        ${IBEXA_TEMPLATE_DIR}/solrconfig.xml


USER solr
ENV SOLR_CORE=collection1
CMD ["bash", "-c", "solr-precreate \"$SOLR_CORE\" \"$IBEXA_TEMPLATE_DIR\""]
