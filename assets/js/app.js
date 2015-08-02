var Miniflux = {};

/**
* @define {boolean}
*/
var COMPILED = false;

Miniflux.App = (function() {

    return {
        Log: function(message) {
            if (! COMPILED) {
               console.log(message);
            }
        },
        Run: function() {
            Miniflux.Event.ListenKeyboardEvents();
            Miniflux.Event.ListenMouseEvents();
            Miniflux.Event.ListenVisibilityEvents();
            this.FrontendUpdateCheck();
        },
        FrontendUpdateCheck: function() {
            var request = new XMLHttpRequest();
            request.onload = function() {
                var response = JSON.parse(this.responseText);

                if (response['frontend_updatecheck_interval'] > 0) {
                    Miniflux.App.Log('Frontend updatecheck interval in minutes: ' + response['frontend_updatecheck_interval']);
                    Miniflux.Item.CheckForUpdates();
                    setInterval(function(){ Miniflux.Item.CheckForUpdates(); }, response['frontend_updatecheck_interval']*60*1000);
                }
                else {
                    Miniflux.App.Log('Frontend updatecheck disabled');
                }

                if(response['scroll_marks_read'] === "1"){ //@todo add check if we are on unread page
                    Miniflux.Event.ListenScrollEvents();
                }
            };

            request.open("POST", "?action=get-config", true);
            request.send(JSON.stringify(['frontend_updatecheck_interval','scroll_marks_read']));
        }
    };

})();
