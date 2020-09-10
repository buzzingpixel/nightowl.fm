/* eslint-disable global-require */

module.exports = {
    purge: {
        enable: false, // We're doing purge as part of the build process
    },
    theme: {
        extend: {
            colors: {
                'night-owl-red': '#bf1e2e',
                'night-owl-dark-red': '#8f1622',

                'night-owl-light-orange': '#f9ae57',
                'night-owl-orange': '#f8931f',
                'night-owl-dark-orange': '#b96e17',

                'night-owl-lighter-dark-blue': '#6d6c72',
                'night-owl-dark-blue': '#3d3c44',
                'night-owl-darker-blue': '#2d2c32',

                'night-owl-lightest-gray': '#f7f7f7',
                'night-owl-lightest-gray-more': '#dddddd',
                'night-owl-lighter-gray': '#cecece',
                'night-owl-gray': '#bebebe',
                'night-owl-dark-gray': '#8e8e8e',
                'night-owl-darker-gray': '#696969',

                'twitter-blue': '#1b95e0',
            },
            fontFamily: {
            },
        },
    },
    variants: {},
    plugins: [
        require('@tailwindcss/ui'),
    ],
};
