/**
 * Created by oliverde8 on 21/02/2015.
 */

(function ( $ ) {


    $.fn.ode8_mp_server = function ( options ) {

        var settings = $.extend({
            login: null,
            mapping : []
        }, options);

        var that = $(this);

        $.ajax({
            url : "/mp/dedicated/api/info/" + settings.login + ".json"
        }).done(function (data){
            console.log(data);

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

                var mapListContainer = that.find('.mp_server__info__map_list');
                if (playerListContainer) {
                    _updateMapList(mapListContainer, data.maps);
                }

            } else {
                console.error("Maniaplanet server Info can't connect to server : " + settings.login)
            }
        });

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
            var mockUpContainer = that.find('.mp_server__info__player_mockup');
            inputContainer.html('');


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

        return this;
    };

    $( document).ready(function () {
        $(".mp_server__info").each(function(){
            var that = $(this);
            that.ode8_mp_server({login: that.data('server-login')});
        });
    });
}( jQuery ));