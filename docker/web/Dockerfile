############
# Build base
############
FROM --platform=$BUILDPLATFORM nginx:1.19.9 as base
ARG TARGETPLATFORM
ARG BUILDPLATFORM
RUN echo "I am running on $BUILDPLATFORM, building for $TARGETPLATFORM"

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
