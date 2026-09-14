#!/usr/bin/env bash

set -e

# Default parameters, if not overloaded by user arguments
DESTINATION_DIR=.platform/configsets/solr8/conf
SOLR_VERSION=8.11.1
FORCE=false
SOLR_INSTALL_DIR=""
ALLOW_URLS_CLI=""

show_help() {
    cat << EOF
Script for generating Solr config
This config can be used to configure solr on Ibexa Cloud (Platform.sh) or elsewhere.
The script should be executed from the Ibexa project root directory.

Help (this text):
./vendor/ibexa/solr/bin/generate-solr-config.sh --help

Usage with Ibexa Cloud (arguments here can be skipped as they have default values):
./vendor/ibexa/solr/bin/generate-solr-config.sh \\
  --destination-dir=.platform/configsets/solr8/conf \\
  --solr-version=8.11.1

Usage with on-premise version of Solr:
./vendor/ibexa/solr/bin/generate-solr-config.sh \\
  --destination-dir=/opt/solr/server/ibexa/template \\
  --solr-install-dir=/opt/solr

Warning:
 This script only supports Solr 7 and higher !!


Arguments:
  [--destination-dir=<dest.dir>]     : Location where solr config should be stored
                                       Default value is .platform/configsets/solr8/conf
  [-f|--force]                       : Overwrite destination-dir if it already exists
  [--solr-install-dir]               : Existing downloaded Solr install to copy base config from.
  [--solr-version]                   : Solr version to download & copy base config from, used only if --solr-install-dir is unset
  [-h|--help]                        : Help text (this text)
EOF
}

realpath() {
    [[ $1 = /* ]] && echo "$1" || echo "$PWD/${1#./}"
}


IBEXA_SCRIPT=`realpath $0`
IBEXA_BUNDLE_PATH="`dirname $IBEXA_SCRIPT`/.."

## Parse arguments
for i in "$@"; do
    case $i in
        --destination-dir=*)
            DESTINATION_DIR="${i#*=}"
            ;;
        -f|--force)
            FORCE=true
            ;;
        --solr-version=*)
            SOLR_VERSION="${i#*=}"
            ;;
        --solr-install-dir=*)
            SOLR_INSTALL_DIR="${i#*=}"
            SOLR_INSTALL_DIR="${SOLR_INSTALL_DIR/#\~/$HOME}"
            ;;
        --allow-urls)
            ALLOW_URLS_CLI="$2"; shift 2
            ;;
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            show_help "${i}"
            exit 1
            ;;
    esac
done

: "${ALLOW_URLS_CLI:=${ALLOW_URLS:-}}"

if [ `whoami` == "root" ]; then
    echo "Error : Do not run this script as root"
    exit 1
fi

if [ -e $DESTINATION_DIR ]; then
    if [ "$FORCE" == "true" ]; then
        echo -e "\033[0;31mDestination directory ($DESTINATION_DIR) already exists, removing in 5 seconds.... \033[0m"
        sleep 5
        rm -Rf $DESTINATION_DIR
    else
        echo -e "\033[1;31mError: Destination dir already exists ($DESTINATION_DIR). Use -f parameter to force \033[0m"
        exit 1
    fi
fi

if [ "$SOLR_INSTALL_DIR" == "" ]; then
    # If we were not provided an existing install directory we'll temporarily download a version of solr to generate config.
    GENERATE_SOLR_TMPDIR=`mktemp -d`
    echo "Downloading solr bundle:"

    # choose archive path based on SOLR version (> 9.0.0 uses solr/solr)
    SOLR_MAJOR=$(echo "$SOLR_VERSION" | sed -E 's/^([0-9]+).*/\1/')
    if [[ "$SOLR_MAJOR" =~ ^[0-9]+$ ]] && [ "$SOLR_MAJOR" -ge 9 ]; then
      DOWNLOAD_BASE="https://archive.apache.org/dist/solr/solr"
    else
      DOWNLOAD_BASE="https://archive.apache.org/dist/lucene/solr"
    fi

    curl "${DOWNLOAD_BASE}/${SOLR_VERSION}/solr-${SOLR_VERSION}.tgz" > "${GENERATE_SOLR_TMPDIR}/solr-${SOLR_VERSION}.tgz"

    echo "Untaring"
    cd $GENERATE_SOLR_TMPDIR
    tar -xzf solr-${SOLR_VERSION}.tgz
    cd - > /dev/null 2>&1
    echo "done extracting Solr"
    SOLR_INSTALL_DIR="${GENERATE_SOLR_TMPDIR}/solr-${SOLR_VERSION}"
fi

mkdir -p $DESTINATION_DIR
cp -a ${IBEXA_BUNDLE_PATH}/src/lib/Resources/config/solr/* $DESTINATION_DIR
cp ${SOLR_INSTALL_DIR}/server/solr/configsets/_default/conf/{solrconfig.xml,stopwords.txt,synonyms.txt} $DESTINATION_DIR

if [[ ! $DESTINATION_DIR =~ ^\.platform ]]; then

    if [[ "$SOLR_MAJOR" =~ ^[0-9]+$ ]] && [ "$SOLR_MAJOR" -ge 9 ]; then
        cp -f ${SOLR_INSTALL_DIR}/server/solr/solr.xml $DESTINATION_DIR/../..

        URL_LIST="${ALLOW_URLS_CLI//,/ }"
        SOLR_XML_PATH="${DESTINATION_DIR}/../../solr.xml"

        if [[ -f "$SOLR_XML_PATH" ]]; then
            # backup original
            cp "$SOLR_XML_PATH" "${SOLR_XML_PATH}.bak"
            # replace inner text of the allowUrls element
            sed -i \
              -e "s|\(<str name=\"allowUrls\">\)[^<]*\(<\/str>\)|\1${URL_LIST}\2|" \
              "$SOLR_XML_PATH"
            echo "NOTE: Updated <str name=\"allowUrls\"> to: ${URL_LIST}"
        else
            echo "WARNING: solr.xml not found at '$SOLR_XML_PATH'; skipping allowUrls patch"
        fi
    else
        # If we are not targeting .platform(.sh) config, we also output default solr.xml
        echo "Copying ${SOLR_INSTALL_DIR}/server/solr/solr.xml to $DESTINATION_DIR/.."

        cp -f ${SOLR_INSTALL_DIR}/server/solr/solr.xml $DESTINATION_DIR/..
    fi
else
    echo "NOTE: Skipped copying ${SOLR_INSTALL_DIR}/server/solr/solr.xml given destination dir is a '.platform/' config folder"
fi

# Adapt autoSoftCommit to have a recommended value, and remove add-unknown-fields-to-the-schema
sed -i.bak '/<updateRequestProcessorChain name="add-unknown-fields-to-the-schema".*/,/<\/updateRequestProcessorChain>/d' $DESTINATION_DIR/solrconfig.xml

if [[ "$SOLR_MAJOR" =~ ^[0-9]+$ ]] && [ "$SOLR_MAJOR" -ge 9 ]; then
  sed -i.bak 's/${solr.autoSoftCommit.maxTime:3000}/${solr.autoSoftCommit.maxTime:20}/' $DESTINATION_DIR/solrconfig.xml
else
    sed -i.bak 's/${solr.autoSoftCommit.maxTime:-1}/${solr.autoSoftCommit.maxTime:20}/' $DESTINATION_DIR/solrconfig.xml
fi

# Configure spellcheck component
sed -i.bak 's/<str name="field">_text_<\/str>/<str name="field">meta_content__text_t<\/str>/' $DESTINATION_DIR/solrconfig.xml
# Add spellcheck component to /select handler
sed -i.bak 's/<requestHandler name="\/select" class="solr.SearchHandler">/<requestHandler name="\/select" class="solr.SearchHandler">\n    <arr name="last-components">\n      <str>spellcheck<\/str>\n    <\/arr>/' $DESTINATION_DIR/solrconfig.xml

rm $DESTINATION_DIR/solrconfig.xml.bak

if [ "$GENERATE_SOLR_TMPDIR" != "" ]; then
    echo Removing temp dir: $GENERATE_SOLR_TMPDIR
    rm -Rf ${GENERATE_SOLR_TMPDIR}
fi

echo -e "\033[0;32mDone generating config to $DESTINATION_DIR ! \033[0m"

if [[ $DESTINATION_DIR =~ ^\.platform ]]; then
    echo "NOTE: You also need to enable solr service in '.platform.app.yaml' and '.platform/services.yaml'."
fi
