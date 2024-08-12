$(document).ready(function () {
    // $('.user_profile').on('click', function () {
    //     if ($('.conversation_box .conversation').hasClass('col-lg-12')) {
    //         $('.conversation_box .conversation').removeClass('col-lg-12').addClass('col-lg-8');
    //     } else {
    //         $('.conversation_box .conversation').removeClass('col-lg-8').addClass('col-lg-12');
    //     }
    // });
    var updateChatListAjax = [];
    var sender = null;
    var receiver = null;
    var cursors = [];
    var getMessageAjax = null;
    var getReceiverAjax = null;
    getSender();
    getChatData();
    let isLoading = false;
    let isMessageLoading = false;
    $('.chat-list').on('scroll', updateChatListLoader);
    $('.mesasges').on('scroll', updateMessageLoader);
    $('.chat-tab').on('click', getMessages);
    $('.send_btn').on('click', function () {
        var message = $('.send_a_message').val();
        var formData = new FormData();
        formData.append("message", message);
    });

    function updateMessageLoader(e) {
        e.preventDefault();
        if (isMessageLoading) return;
        var cursor = $('#message_cursor').val();
        if (cursor !== '') {
            var chat_id = $('#chat-message').data('chat');
            isMessageLoading = true;
            var scrollTop = $(this).scrollTop();
            if (scrollTop <= 2) {
                $('#message-loader').show();
                $.ajax({
                    url: getMessageChatCursorRoute.replace(":chat_id", chat_id).replace(":cursor", cursor),
                    type: "GET",
                    success: function (response) {
                        if (response.success) {
                            if (response.messages.length > 0) {
                                var html = ``;
                                var messages = response.messages;
                                messages.forEach(message => {
                                    html += `<li class="`;
                                    html += message['is_sender'] == 0 ? 'not_me' : 'is_me';
                                    html += `">`;
                                    html += `<span class="skel_img"></span>`;
                                    if (message['deleted'] == 0) {
                                        html += `<span class="message_text">`;
                                        var text = message['text'];
                                        if (text && text.indexOf('\n') !== -1) {
                                            html += text.replace(/\n/g, '<br>');
                                        } else {
                                            html += text ? text : '';
                                        }
                                        html += `</span></li>`;
                                    } else {
                                        html += `<span class="message_text" style="`;
                                        html += `padding: 2px 10px; height: fit-content;`;
                                        html += `background-color: #f4f2ee; color: #000;`;
                                        html += `border: 1px solid #343434; box-shadow: inset 4px 4px 4px #8c8c8c,`;
                                        html += ` inset -4px -4px 4px #8c8c8c, 4px 4px 4px #414141;">`;
                                        html += `This message has been deleted.`;
                                        html += `</span></li>`;
                                    }
                                });
                                $('#chat-message>ul').prepend(html);
                                if (response.cursor) {
                                    $('#message_cursor').val(response.cursor);
                                } else {
                                    $('#message_cursor').val('');
                                }
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        $('#message-loader').hide();
                    },
                    complete: function () {
                        $('#message-loader').hide();
                        isMessageLoading = false;
                        if (receiver == null) {
                            getReceiver(chat_id);
                        } else {
                            var messages = $('.is_me');
                            messages.each(function () {
                                var message = $(this);
                                var imgTag = $('<img>').attr('src', sender['profile_picture_url']);
                                message.find('.skel_img').replaceWith(imgTag).removeClass('.skel_img');
                            });
                            var messages = $('.not_me');
                            messages.each(function () {
                                var message = $(this);
                                var imgTag = $('<img>').attr('src', receiver['profile_picture_url']);
                                message.find('.skel_img').replaceWith(imgTag).removeClass('.skel_img');
                            });
                        }
                        $('#chat-message').animate({ scrollTop: $('#chat-message')[0].scrollHeight }, 'slow');
                    },
                });
            } else {
                isMessageLoading = false;
            }
        }
    }

    function getChatData() {
        var chats = $('.skel-chat');
        chats.each(function () {
            var chat = $(this);
            getProfileMessage(chat);
            getLatestMessage(chat);
            chat.removeClass('skel-chat');
        });
        $('.chat-tab').on('click', getMessages);
    }

    function getProfileMessage(chat) {
        $.ajax({
            url: getChatProfile.replace(":profile_id", chat.data()['profile']),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    var profile = response.user_profile;
                    chat.find('.chat_name').html(profile['first_name'] + ' ' + profile['last_name']);
                    chat.find('.chat_name').removeClass('skel_chat_name');
                    var imgTag = $('<img>').attr('src', profile['profile_picture_url']);
                    chat.find('.chat_image').replaceWith(imgTag);
                } else {
                    chat.remove();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
        });
    }

    function getLatestMessage(chat) {
        $.ajax({
            url: getLatestMessageRoute.replace(":chat_id", chat.prop('id')),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    if (response.message && response.message[0]['text']) {
                        let input = response.message[0]['text'];
                        if (input.length > 25) {
                            let trimmed_text = input.substring(0, 25) + '...';
                            response.message[0]['text'] = trimmed_text;
                        }
                    }
                    if (response.message && response.message[0]['timestamp']) {
                        let date = new Date(response.message[0]['timestamp']);
                        let options = { day: '2-digit', month: 'short' };
                        response.message[0]['timestamp'] = date.toLocaleDateString('en-GB', options);
                    }
                    chat.find('.latest_message').html(response.message[0]['text']);
                    chat.find('.latest_message').removeClass('skel_latest_message');
                    chat.find('.latest_message_timestamp').html(response.message[0]['timestamp']);
                    chat.find('.latest_message_timestamp').removeClass('skel_latest_message_timestamp');
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
        });
    }

    function updateChatListLoader(e) {
        e.preventDefault();
        if (isLoading) return;
        var contentHeight = $(this).prop('scrollHeight');
        var visibleHeight = $(this).height();
        var scrollTop = $(this).scrollTop();
        if (scrollTop + visibleHeight >= contentHeight - 1) {
            $('#chat-loader').show();
            updateChatList();
        }
    }

    function updateChatList() {
        var cursor = $('#chat_cursor').val();
        var html = ``;
        isLoading = true;
        var ajaxRequest = $.ajax({
            url: getRemainMessage.replace(":cursor", cursor),
            type: "GET",
            success: function (response) {
                if (response.success) {
                    if (response.chats.length > 0) {
                        var chats = response.chats;
                        chats.forEach(chat => {
                            if (chat['folder'].includes('INBOX_LINKEDIN_CLASSIC')) {
                                html += `<li class="d-flex chat-tab skel-chat" id="`;
                                html += chat['id'] + `" data-profile="`;
                                html += chat['attendee_provider_id'] + `">`;
                                if (chat['unread'] == 1) {
                                    html += `<span class="unread_count">` + chat['unread_count'] + `</span>`;
                                }
                                html += `<span class="chat_image skel_chat_img"></span>`;
                                html += `<div class="d-block">`;
                                html += `<strong class="chat_name skel_chat_name"></strong>`;
                                html += `<span class="latest_message skel_latest_message"></span>`;
                                html += `</div><div class="date latest_message_timestamp skel_latest_message_timestamp"></div>`;
                                html += `<div class="linkedin"><a href="javascript:;"><i class="fa-brands fa-linkedin">`;
                                html += `</i></a></div></li>`;
                            }
                        });
                        $('.chat-list').append(html);
                        if (response.cursor) {
                            $('#chat_cursor').val(response.cursor);
                            if (!cursors.includes(cursor)) {
                                cursors.push(cursor);
                            }
                        } else {
                            $('#chat_cursor').val('');
                        }
                        getChatData();
                    } else {
                        $('#chat-loader').hide();
                    }
                } else {
                    $('#chat-loader').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                $('#chat-loader').hide();
            },
            complete: function () {
                $('#chat-loader').hide();
                isLoading = false;
                updateChatListAjax = updateChatListAjax.filter(req => req !== ajaxRequest);
            },
        });
        updateChatListAjax.push(ajaxRequest);
    }

    function getMessages() {
        if (getMessageAjax) {
            getMessageAjax.abort();
        }
        var chat_id = $(this).attr("id");
        getMessageAjax = $.ajax({
            url: getMessageChatRoute.replace(":chat_id", chat_id),
            type: "GET",
            beforeSend: function () {
                var html = ``;
                html = `<li class="not_me"><span class="skel_img"></span>
                        <span class="message_text skel_text"></span></li>
                        <li class="is_me"><span class="skel_img"></span>
                        <span class="message_text skel_text"></span></li>
                        <li class="not_me"><span class="skel_img"></span>
                        <span class="message_text skel_text"></span></li>
                        <li class="is_me"><span class="skel_img"></span>
                        <span class="message_text skel_text"></span></li>`;
                $('#chat-message>ul').html(html);
            },
            success: function (response) {
                if (response.success) {
                    if (response.messages.length > 0) {
                        $('#' + chat_id + ' .unread_count').remove();
                        var html = ``;
                        var messages = response.messages;
                        messages.forEach(message => {
                            html += `<li class="`;
                            html += message['is_sender'] == 0 ? 'not_me' : 'is_me';
                            html += `">`;
                            html += `<span class="skel_img"></span>`;
                            if (message['deleted'] == 0) {
                                html += `<span class="message_text">`;
                                var text = message['text'];
                                if (text && text.indexOf('\n') !== -1) {
                                    html += text.replace(/\n/g, '<br>');
                                } else {
                                    html += text ? text : '';
                                }
                                html += `</span></li>`;
                            } else {
                                html += `<span class="message_text" style="`;
                                html += `padding: 2px 10px; height: fit-content;`;
                                html += `background-color: #f4f2ee; color: #000;`;
                                html += `border: 1px solid #343434; box-shadow: inset 4px 4px 4px #8c8c8c,`;
                                html += ` inset -4px -4px 4px #8c8c8c, 4px 4px 4px #414141;">`;
                                html += `This message has been deleted.`;
                                html += `</span></li>`;
                            }
                        });
                        $('#chat-message>ul').html(html);
                        if (response.cursor) {
                            $('#message_cursor').val(response.cursor);
                        } else {
                            $('#message_cursor').val('');
                        }
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
            complete: function () {
                $('#chat-message').attr('data-chat', chat_id);
                getMessageAjax = null;
                if ($('#' + chat_id).data('disable')) {
                    $('.conversation>.send_form>input').remove();
                    $('.conversation>.send_form').css({
                        visibility: 'hidden',
                    });
                } else {
                    var html = `<input type="text" placeholder="Send a message" name="send_a_message" class="send_a_message">
                                <input type="button" class="send_btn" value="send">`;
                    $('.conversation>.send_form').html(html);
                    $('.conversation>.send_form').css({
                        visibility: 'visible',
                    });
                }
                $('#chat-message').animate({ scrollTop: $('#chat-message')[0].scrollHeight }, 'slow');
                getReceiver(chat_id);
            },
        });
    }

    function getSender() {
        if (sender == null) {
            $.ajax({
                url: getChatSender,
                type: "GET",
                success: function (response) {
                    if (response.success) {
                        sender = response.sender;
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                },
                complete: function () {
                    var chat = $('.chat-tab')[0];
                    $(chat).click();
                },
            });
        }
    }

    function getReceiver(chat_id) {
        if (getReceiverAjax) {
            getReceiverAjax.abort();
        }
        if (sender != null) {
            var messages = $('.is_me');
            messages.each(function () {
                var message = $(this);
                var imgTag = $('<img>').attr('src', sender['profile_picture_url']);
                message.find('.skel_img').replaceWith(imgTag).removeClass('.skel_img');
            });
        }
        getReceiverAjax = $.ajax({
            url: getChatReceiver.replace(':chat_id', chat_id),
            type: "GET",
            beforeSend: function () {
                var html = ``;
                html = `<img class="skel_img" src="" alt=""><h6 class="skel_head"></h6>
                        <span class="user_name skel_user_name"></span>
                        <span class="user_email skel_user_email"></span>
                        <div class="note skel_text"></div>`;
                $('.conversation_info>.info').html(html);
            },
            success: function (response) {
                if (response.success) {
                    receiver = response.receiver;
                    var messages = $('.not_me');
                    messages.each(function () {
                        var message = $(this);
                        var imgTag = $('<img>').attr('src', response.receiver['profile_picture_url']);
                        message.find('.skel_img').replaceWith(imgTag).removeClass('.skel_img');
                    });
                    var img = $('.conversation_info .info .skel_img');
                    img.prop('src', response.receiver['profile_picture_url']);
                    img.removeClass('skel_img');
                    var head = $('.conversation_info .info .skel_head');
                    var connection = '';
                    if (response.receiver['network_distance']) {
                        if (response.receiver['network_distance'] == 'FIRST_DEGREE') {
                            connection = '1st';
                        } else if (response.receiver['network_distance'] == 'SECOND_DEGREE') {
                            connection = '2nd';
                        } else if (response.receiver['network_distance'] == 'THIRD_DEGREE') {
                            connection = '3rd';
                        }
                    }
                    head.html(response.receiver['first_name'] + ' ' + response.receiver['last_name'] + ' ' + connection);
                    head.removeClass('skel_head');
                    var user_name = $('.conversation_info .info .skel_user_name');
                    user_name.html(response.receiver['headline']);
                    user_name.removeClass('skel_user_name');
                    if (response.receiver['contact_info'] && response.receiver['contact_info']['emails']) {
                        var user_email = $('.conversation_info .info .skel_user_email');
                        user_email.html('<a href="mailto:' + response.receiver['contact_info']['emails'][0] + '">' + response.receiver['contact_info']['emails'][0] + '</a>');
                        user_email.removeClass('skel_user_email');
                    } else {
                        var user_email = $('.conversation_info .info .skel_user_email');
                        user_email.removeClass('skel_user_email');
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
            complete: function () {
                getReceiverAjax = null;
            }
        });
    }

    function getLatestChat() {
        var html = ``;
        $.ajax({
            url: getLatestChatRoute,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    var chats = response.chats;
                    chats.forEach(chat => {
                        if ($('#' + chat['id']).length > 0) {
                            var chat_tabs = $('.chat-tab');
                            // html += `<li class="d-flex chat-tab skel-chat" id="`;
                            // html += chat['id'] + `" data-profile="`;
                            // html += chat['attendee_provider_id'] + `">`;
                            // if (chat['unread'] == 1) {
                            //     html += `<span class="unread_count">` + chat['unread_count'] + `</span>`;
                            // }
                            // html += `<span class="chat_image skel_chat_img"></span>`;
                            // html += `<div class="d-block">`;
                            // html += `<strong class="chat_name skel_chat_name"></strong>`;
                            // html += `<span class="latest_message skel_latest_message"></span>`;
                            // html += `</div><div class="date latest_message_timestamp skel_latest_message_timestamp"></div>`;
                            // html += `<div class="linkedin"><a href="javascript:;"><i class="fa-brands fa-linkedin">`;
                            // html += `</i></a></div></li>`;
                        }
                    });
                    $('.chat-list').prepend(html);
                    // getChatData();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            },
        });
    }

    setInterval(function () {
        getLatestChat();
    }, 60000);

    setInterval(function () {
        console.log(updateChatListAjax);
        updateChatList();
    }, 120000);
});