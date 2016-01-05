#!/usr/bin/env bash

# Create API token, use at your own risk, this script has not been tested much.

# borrowed from https://gist.github.com/cdown/1163649
urlencode() {
    # urlencode <string>

    local length="${#1}"
    for (( i = 0; i < length; i++ )); do
        local c="${1:i:1}"
        case $c in
            [a-zA-Z0-9.~_-]) printf "$c" ;;
            *) printf '%s' "$c" | xxd -p -c1 |
                   while read c; do printf '%%%s' "$c"; done ;;
        esac
    done
}

while [[ "$username" == "" ]];
do
    read -p "Enter user name/email address (will not be saved, press CTRL-C to abort): " username
done

while [[ "$password" == "" ]];
do
    read -p "Enter password (will visible but not saved; press CTRL-C to abort): " password
done

while [[ "$hostname" == "" ]];
do
    read -p "Enter Seafile server hostname (without schema and slashes, press CTRL-C to abort): " hostname
done

token_path=~/.seafile-php-sdk/api-token.json
token_dir=`dirname ${token_path}`

mkdir -p ${token_dir}

if [[ -f "${token_path}" ]]; then
    read -p "File ${token_path} already exists. Overwrite? (y/N): " overwrite

    if [[ "${overwrite}" != "y" ]]; then
        echo "Existing token left alone."
        exit 1
    fi
fi

usernameUrlEncoded=`urlencode ${username}`

curl -d "username=${usernameUrlEncoded}&password=${password}" https://${hostname}/api2/auth-token/ > ${token_path}

if [[ $? -eq "0" ]]; then
    echo $?
    echo "Token saved to ${token_path}"
else
    echo $?
    echo "Error retrieving token."
fi
