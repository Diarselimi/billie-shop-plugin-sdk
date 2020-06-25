#!/usr/bin/env bash

command -v git >/dev/null 2>&1 || {
    echo "Installing git..."
    apt-get update
    apt-get install -y git
}

command -v composer >/dev/null 2>&1 || {
    echo "Installing composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
}
