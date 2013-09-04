"use strict";

var APP = {
    
    config  : {
        url     : {
            base    : CONFIG.base_url
        }
    },

    // initialize the app
    init : function() {
        console.log( 'Application loaded!' );
    }
};

$( document ).ready( function() {
    APP.init();
});