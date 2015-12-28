#!/usr/bin/env bash

# Create test config, use at your own risk, this script has not been tested much.

while [[ "$baseUri" == "" ]];
do
    read -p "Enter baseUri (for example 'https://your-seafile-server.example.com', press CTRL-C to abort): " baseUri
done

while [[ "$testLibId" == "" ]];
do
    read -p "Enter encrypted test library ID (press CTRL-C to abort): " testLibId
done

while [[ "$testLibPassword" == "" ]];
do
    read -s -p "Enter encrypted test library password (will be saved in clear text, press CTRL-C to abort): " testLibPassword
    echo ""
done

cfg_path=~/.seafile-php-sdk/cfg.json
cfg_dir=`dirname ${cfg_path}`

mkdir -p ${cfg_dir}

if [[ -f "${cfg_path}" ]]; then
    read -p "File ${cfg_path} already exists. Overwrite? (y/N): " overwrite

    if [[ "${overwrite}" != "y" ]]; then
        echo "Existing cfg left alone."
        exit 1
    fi
fi

echo "
{
        \"baseUri\": \"${baseUri}\",
        \"testLibId\": \"${testLibId}\",
        \"testLibPassword\": \"${testLibPassword}\"
}
" > $cfg_path

echo "Cfg saved to ${cfg_path}"