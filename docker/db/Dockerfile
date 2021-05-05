FROM --platform=$BUILDPLATFORM  postgres:9.6.16
ARG TARGETPLATFORM
ARG BUILDPLATFORM
RUN echo "I am running on $BUILDPLATFORM, building for $TARGETPLATFORM"

# Update bash profile
COPY ./docker/.profile /root/.profile
COPY ./docker/.profile /root/.bashrc
