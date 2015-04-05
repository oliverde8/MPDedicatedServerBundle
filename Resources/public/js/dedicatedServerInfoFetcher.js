/**
 * Created by oliverde8 on 21/02/2015.
 */

(function ( $ ) {

    var ode8_mp_server = function(element, options) {

        var defaults = {
            login: null,
            server_info_url: null,
            server_maps_url: null,
            server_chat_url: null,
            mapping : []
        }

        var that = $(element);
        var plugin = this;
        plugin.settings = {};

        var _updateServerOption = function(that, data) {
            for (var key in data) {
                var value = data[key];
                switch (key) {
                    case 'description' :
                    case 'name' :
                        value = MPStyle.Parser.toHTML(value);
                        break;
                    case 'ladderServerLimitMax' :
                        value = value/1000;
                }
                that.find('.mp_server__info__' + key).html(value);
            }
        };

        var _updatePlayerList = function(that, data) {
            var inputContainer = that.find('.mp_server__info__player_container');
            inputContainer.html('');
            var mockUpContainer = that.find('.mp_server__info__player_mockup');


            if (inputContainer && mockUpContainer) {
                for (var key in data) {
                    mockUpContainer.find('.mp_server__info__player__nb').html(parseInt(key)+1);
                    for(var key2 in data[key]) {
                        var value = data[key][key2];
                        switch (key2) {
                            case 'nickName' :
                                value = MPStyle.Parser.toHTML(value);
                            default :
                                mockUpContainer.find('.mp_server__info__player__' + key2).html(value);
                        }
                    }
                    mockUpContainer.clone().appendTo(inputContainer);
                }
            }
        };

        var _updateCurrentMap = function(that, data) {
            for (var key in data) {
                var value = data[key];
                switch (key) {
                    case 'author' :
                    case 'name' :
                        value = MPStyle.Parser.toHTML(value);
                        break;
                }
                that.find('.mp_server__info__currentMap__' + key).html(value);
            }
        };

        var _updateMapList = function(that, data) {
            var inputContainer = that.find('.mp_server__info__map_container');
            inputContainer.html('');
            var mockUpContainer = that.find('.mp_server__info__map_mockup');

            if (inputContainer && mockUpContainer) {
                for (var key in data) {
                    mockUpContainer.find('.mp_server__info__map__nb').html(parseInt(key)+1);
                    for(var key2 in data[key]) {
                        var value = data[key][key2];
                        switch (key2) {
                            case 'name' :
                            case 'author' :
                                value = MPStyle.Parser.toHTML(value);
                            default :
                                mockUpContainer.find('.mp_server__info__map__' + key2).html(value);
                        }
                    }
                    mockUpContainer.clone().appendTo(inputContainer);
                }
            }
        };

        var _updateChatLines = function(that, data) {
            var inputContainer = that.find('.mp_server__info__chat_container');
            inputContainer.html('');
            var mockUpContainer = that.find('.mp_server__info__chat_mockup');

            if (inputContainer && mockUpContainer) {
                for (var key in data) {
                    mockUpContainer.find('.mp_server__info__chat_line').html(MPStyle.Parser.toHTML(data[key]));
                    mockUpContainer.clone().appendTo(inputContainer);
                }
            }
        };

        plugin.init = function() {

            plugin.settings = $.extend({}, defaults, options);

            plugin.updateServerInfo();
            plugin.updateMapList();
            plugin.updateChatLines();
        };

        plugin.updateServerInfo = function() {
            $.ajax({
                url: plugin.settings.server_info_url
            }).done(function (data) {

                if (!data.error) {
                    _updateServerOption(that, data.serverOptions);
                    _updateCurrentMap(that, data.currentMap);

                    that.find('.mp_server__info__nbPlayers').html(data.serverPlayers.length);
                    that.find('.mp_server__info__nbSpectators').html(data.serverSpectators.length);

                    var playerListContainer = that.find('.mp_server__info__player_list');
                    if (playerListContainer) {
                        _updatePlayerList(playerListContainer, data.serverPlayers);
                    }

                    var playerListContainer = that.find('.mp_server__info__spectator_list');
                    if (playerListContainer) {
                        _updatePlayerList(playerListContainer, data.serverSpectators);
                    }

                } else {
                    console.error("Maniaplanet server Info can't connect to server : " + plugin.settings.login)
                }
            });
        };

        plugin.updateMapList = function() {
            var mapListContainer = that.find('.mp_server__info__map_list');
            if (mapListContainer && plugin.settings.server_maps_url != null) {
                $.ajax({
                    url: plugin.settings.server_maps_url
                }).done(function (data) {
                    _updateMapList(mapListContainer, data);
                });
            }
        };

        plugin.updateChatLines = function() {
            var chatLinesContainer = that.find('.mp_server__info__chat');
            if (chatLinesContainer && plugin.settings.server_chat_url != null) {
                $.ajax({
                    url: plugin.settings.server_chat_url
                }).done(function (data) {
                    _updateChatLines(chatLinesContainer, data);
                });
            }
        };

        var autChatUpdateTimerId = null;
        plugin.startAutoChatUpdate = function(delay) {
            if (autChatUpdateTimerId == null) {
                autChatUpdateTimerId = window.setInterval(function () {
                    plugin.updateChatLines();
                }, delay);
            }
        };

        plugin.stopAutoChatUpdate = function () {
            if (autChatUpdateTimerId != null) {
                window.clearInterval(autChatUpdateTimerId);
            }
        };

        plugin.init();

    };

    // add the plugin to the jQuery.fn object
    $.fn.ode8_mp_server = function(options) {
        return this.each(function() {
            if (undefined == $(this).data('ode8_mp_server')) {
                var plugin = new ode8_mp_server(this, options);
                $(this).data('ode8_mp_server', plugin);
            }
        });
    };

    $(document).ready(function () {
        $(".mp_server__info").each(function(){
            var that = $(this);
            that.ode8_mp_server(
                {
                    login: that.data('server-login'),
                    server_info_url: that.data('server-info-url'),
                    server_maps_url: that.data('server-maps-url'),
                    server_chat_url: that.data('server-chat-url')
                }
            );
            that.data('ode8_mp_server').startAutoChatUpdate(10000);
        });
    });
}( jQuery ));