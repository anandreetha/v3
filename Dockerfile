# Note: 
#     registry.sasyadev.com/php:7.2-fpm is a local copy of php:7.2-fpm


# Build modules first
FROM    registry.sasyadev.com/php:7.2-fpm AS phalcon-build
RUN     apt-get update && apt-get -y install git libcurl3-dev libpng-dev libjpeg-dev
RUN     docker-php-ext-configure gd --with-jpeg-dir=/usr/include
RUN     docker-php-ext-install -j$(nproc) curl pdo pdo_mysql mbstring session gd
RUN     pecl install psr-0.6.0
RUN     pecl install redis-4.3.0
RUN     pecl install mongodb-1.5.5
RUN     pecl install xdebug-2.7.2
RUN     git clone "git://github.com/phalcon/cphalcon.git" --branch v3.4.3 --single-branch /tmp/cphalcon
WORKDIR /tmp/cphalcon/build
RUN     ./install
RUN     ln -s "$(php-config --extension-dir)" /build_extensions

# Copy modules into clean image
FROM registry.sasyadev.com/php:7.2-fpm
RUN  ln -s "$(php-config --extension-dir)" /usr/local/lib/php/extensions/current
COPY --from=phalcon-build /build_extensions/curl.so \
                  /build_extensions/pdo.so \
                  /build_extensions/pdo_mysql.so \
                  /build_extensions/mbstring.so \
                  /build_extensions/session.so \
                  /build_extensions/gd.so \
                  /build_extensions/psr.so \
                  /build_extensions/redis.so \
                  /build_extensions/mongodb.so \
                  /build_extensions/phalcon.so \
                  /build_extensions/xdebug.so \
                  /usr/local/lib/php/extensions/current/
# Copy shared libs linked to by above objects
COPY --from=phalcon-build /usr/lib/x86_64-linux-gnu/libpng16.so.16 \
                  /usr/lib/x86_64-linux-gnu/libjpeg.so.62 \
                  /usr/lib/x86_64-linux-gnu/
RUN echo "upload_max_filesize = 20M;\npost_max_size = 20M;" > /usr/local/etc/php/conf.d/uploads.ini
ENV MAX_CHILDREN=5
ENV MAX_SPARE_SERVERS=3
ENV MIN_SPARE_SERVERS=1
ENV START_SERVERS=2
RUN sed -i      's/pm.max_children = 5/pm.max_children = ${MAX_CHILDREN}/g'      /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = ${MAX_SPARE_SERVERS}/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = ${MIN_SPARE_SERVERS}/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i     's/pm.start_servers = 2/pm.start_servers = ${START_SERVERS}/g'     /usr/local/etc/php-fpm.d/www.conf

# Enable copied modules. Manually handle phalcon with some Zs in order to load it after psr
# If we do not do this, php can't even start because phalcon bombs out looking for psr on startup.
RUN docker-php-ext-enable curl pdo pdo_mysql mbstring session gd psr redis mongodb && \
    echo 'extension=phalcon.so' > /usr/local/etc/php/conf.d/docker-php-ext-zz-phalcon.ini
# Note that xdebug is intentionally not enabled here.
# Pass `-d zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20170718/xdebug.so` to cmdline if needed

