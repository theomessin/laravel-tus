FROM php:7-cli

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive

# Or your actual UID, GID on Linux if not the default 1000
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

RUN apt-get update \
    && apt-get -y install --no-install-recommends apt-utils dialog 2>&1 \
    #
    #
    && apt-get -y install curl git openssl unzip zip \
    && apt-get -y install software-properties-common \
    && apt-get -y install procps iproute2 lsb-release \
    #
    && groupadd --gid $USER_GID $USERNAME \
    && useradd -s /bin/bash --uid $USER_UID --gid $USER_GID -m $USERNAME \
    #
    && apt-get install -y sudo \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME \
    #
    && apt-get autoremove -y \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/*

# Install Composer from: https://hub.docker.com/_/composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> /home/vscode/.bashrc \
    && su - vscode -c "composer global require hirak/prestissimo"

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=
