/* eslint-disable global-require */

module.exports = {
    purge: {
        enable: false, // We're doing purge as part of the build process
    },
    theme: {
        extend: {
            colors: {
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
