############
# Build base
############
FROM nginx:1.17.5 as base

# Update bash profile
COPY ./docker/.profile /root/.profile
COPY ./docker/.profile /root/.bashrc

# Copy configuration into container
COPY ./docker/web/dhparam.pem /etc/nginx/ssl/dhparam.pem





##################
# Build dev target
##################
FROM base as dev

# Copy dev config
COPY ./docker/web/nginx-local.conf /etc/nginx/conf.d/local.conf





###################
# Build prod target
###################
FROM base as prod

# Copy prod config
COPY ./docker/web/nginx-prod.conf /etc/nginx/conf.d/prod.conf

# Copy the project files into the image for optimal production performance
COPY . /opt/project
