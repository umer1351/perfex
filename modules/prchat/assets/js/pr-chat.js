var offsetPush = {};
var endOfScroll = {};

function removeActiveChatWindow(id) {
  var activeChatWindows = getActiveChatWindowsFromStorage();
  var indexToRemove = null;

  $.each(activeChatWindows, function (index, obj) {
    if (obj.id == id) {
      indexToRemove = index;
    }
  });
  if (indexToRemove !== null) {
    activeChatWindows.splice(indexToRemove, 1);
  }

  localStorage.activeChatWindows = JSON.stringify(activeChatWindows);
}

function addActiveChatWindow(obj) {
  if (typeof localStorage.activeChatWindows == "undefined") {
    localStorage.activeChatWindows = "";
  }

  if (isChatBoxInLocalStorageActiveChats(obj.id)) {
    return false;
  }

  var currentActiveChatWindows = getActiveChatWindowsFromStorage();

  currentActiveChatWindows.push(obj);

  localStorage.activeChatWindows = JSON.stringify(currentActiveChatWindows);
}

function getActiveChatWindowsFromStorage() {
  if (typeof localStorage.activeChatWindows == "undefined") {
    return [];
  }

  var activeChatWindows = localStorage.activeChatWindows;

  if (activeChatWindows == "") {
    return [];
  }

  return JSON.parse(activeChatWindows);
}

function isChatBoxInLocalStorageActiveChats(id) {
  var retVal = false;
  $.each(getActiveChatWindowsFromStorage(), function (index, obj) {
    if (obj.id == id) {
      retVal = true;
    }
  });

  return retVal;
}

function isVideoUrl(url) {
  var youtubeRegex = /(youtube(-nocookie)?\.com|youtu\.be)/i;
  var vimeoRegex = /(vimeo(pro)?.com)/i;
  var facebookVideoRegex = /(facebook\.com)\/([a-z0-9_-]*)\/videos\//i;
  var googlemapsRegex = /((maps|www)\.)?google\.([^\/\?]+)\/.*maps/i;

  return (
    youtubeRegex.test(url) ||
    vimeoRegex.test(url) ||
    facebookVideoRegex.test(url) ||
    googlemapsRegex.test(url)
  );
}

function createTextLinks_(text) {
  var regex = /\.(gif|jpg|jpeg|tiff|png|swf)$/i;
  return (text || "").replace(
    /([^\S]|^)(((https?\:\/\/)|(www\.))(\S+))/gi,
    function (match, string, url) {
      var hyperlink = url;
      if (!hyperlink.match("^https?://")) {
        hyperlink = "//" + hyperlink;
      }
      if (hyperlink.match("^http?://")) {
        hyperlink = hyperlink.replace("http://", "//");
      }
      if (hyperlink.match(regex)) {
        return (
          string +
          '<a href="' +
          hyperlink +
          '" target="blank"><img style="width:100%;height:100%;padding-top:2px;" rel="nofollow" src="' +
          hyperlink +
          '"/></a>'
        );
      } else if (isVideoUrl(hyperlink)) {
        return (
          string +
          '<a href="' +
          hyperlink +
          '" target="blank" data-video-embed="true" rel="nofollow">' +
          url +
          "</a>"
        );
      } else {
        return (
          string +
          '<a target="blank" rel="nofollow" href="' +
          hyperlink +
          '">' +
          url +
          "</a>"
        );
      }
    }
  );
}

function clearSearchValues() {
  $(".searchBox").slideUp(200, function () {
    $(".searchBox").addClass("inputHidden");
    $(".searchBox").val("");
    $("#members-list a").filter(function () {
      $(this).css("display", "block");
    });
  });
}

function changeColor(obj) {
  var url = $(obj).attr("action");
  var color = $(obj).find("input[name=color]").val();
  getCurrentBackgound = color;
  $.post(url, {
    color: color,
  }).done(function (r) {
    r = JSON.parse(r);
    if (r.success === "unknownColor") {
      alert_float("warning", prchatSettings.invalidColor);
      return false;
    }

    if (r.success !== false) {
      var newColor = r.success;
      if (
        newColor.indexOf("linear-gradient(") > -1 &&
        newColor.indexOf(");") > -1
      ) {
        location.reload();
        return false;
      } else {
        color = color;
      }
      $("#pusherChat #membersContent .topInfo").css("background", color);
      $("#pusherChat chatHead").css("background", color);
      $("#pusherChat .chat-footer").css("background", color);
      $("#pusherChat .pusherChatBox .msgTxt p.you").css("background", color);
      changeHoverColor(color);
      return false;
    }
  });
}

function changeHoverColor(color) {
  $("#members-list a, .dropup li a").filter(function () {
    $(this).hover(
      function () {
        $(this).css("background", color);
      },
      function () {
        $(this).css("background", "");
      }
    );
  });
}

/*---------------* function that handles updating unread messages *---------------*/
function updateUnreadMessages(currentElement, pusherChatBox) {
  if (pusherChatBox) {
    var linkId = pusherChatBox.attr("id").replace("id_", "");
    var memberContentLinkId = $("#membersContent a#" + linkId);
    pusherChatBox.find(".notification-count").text("0");
    pusherChatBox.find(".notification-box").hide();
    memberContentLinkId.find(".unread-notifications").remove();
    memberContentLinkId.removeClass("animated flash");
    updateLatestMessages(linkId);
    return false;
  }
  if (currentElement) {
    var id = $(currentElement)
      .parents(".pusherChatBox")
      .attr("id")
      .replace("id_", "");
    if (id) {
      var notiVal = $(currentElement)
        .parents(".pusherChatBox")
        .find(".notification-count")
        .text();
      if (notiVal > 0) {
        updateLatestMessages(id);
        $("#membersContent a#" + id).removeClass("animated flash");
        $(".pusherChatBox#id_" + id)
          .find(".notification-count")
          .text("0");
      }
    }
  }
}

/*---------------*  Function removeChatMember and addChatMember must remain untouched and not moved to another place ! *---------------*/
var pendingRemoves = [];

function addChatMember(members) {
  var pendingRemoveTimeout = pendingRemoves[members.id];
  $("a#" + members.id)
    .addClass("on")
    .removeClass("off");
  $(".pusherChatBox#id_" + members.id)
    .addClass("on")
    .removeClass("off");

  if (!$("a#" + members.id).hasClass(members.info.status)) {
    $("a#" + members.id).addClass(members.info.status);
  }
  if (!$(".pusherChatBox#id_" + members.id).hasClass(members.info.status)) {
    $(".pusherChatBox#id_" + members.id).addClass(members.info.status);
  }
  if (pendingRemoveTimeout) {
    clearTimeout(pendingRemoveTimeout);
  }
}

function removeChatMember(members) {
  pendingRemoves[members.id] = setTimeout(function () {
    $("a#" + members.id)
      .removeClass("on " + members.info.status)
      .addClass("off");
    $(".pusherChatBox#id_" + members.id)
      .addClass("off")
      .removeClass("on stillActive " + members.info.status);
    chatMemberUpdate();
  }, 5000);
}

/*-----------------------------* reorganize the chat box position on adding or removing users * -----------------------------*/
function updateBoxPosition() {
  const chatBoxes = $(".chatBoxslide .pusherChatBox:visible");
  let totalWidth = 0;
  let hasOverFlow = false;

  // Position each chat box and check for overflow
  chatBoxes.each(function () {
    const chatBoxWidth = $(this).width();

    $(this).css({ right: totalWidth });
    totalWidth += chatBoxWidth + 20;

    if ($(this).offset().left - 20 < 0) {
      $(this).addClass("overFlow");
      hasOverFlow = true;
    } else {
      $(this).removeClass("overFlow");
    }
  });

  // Set the width of chatBoxslide
  $(".chatBoxslide").css({ width: totalWidth });

  // Manage visibility of slideLeft button
  $("#slideLeft").toggle(hasOverFlow);

  // Manage visibility of slideRight button
  const isOverFlowHideNotEmpty = Boolean($(".overFlowHide").html());
  $("#slideRight").toggle(isOverFlowHideNotEmpty);
}

/*---------------* chatMemberUpdate() place & update users on user page, unred messages notifications *---------------*/
function chatMemberUpdate(subscribed_event) {
  var insertId = "";
  var notification = "";

  $.get(prchatSettings.usersList, function (data) {
    var offlineUser = "";
    var onlineUser = "";
    data = JSON.parse(data);
    $.each(data, function (user_id, value) {
      if (value.staffid != presenceChannel.members.me.id) {
        user = presenceChannel.members.get(value.staffid);

        if (
          value.status != undefined &&
          value.status.length != undefined &&
          value.status == "online"
        ) {
          value.status = "";
        }
        var user_status = "" == value.status ? "online" : value.status;
        var translated_status = "";
        for (var status in chat_user_statuses) {
          if (status == user_status) {
            translated_status = chat_user_statuses[status];
          }
        }
        if (user != null) {
          onlineUser +=
            '<a data-status="' +
            value.status +
            '" data-toggle="tooltip" title="' +
            translated_status +
            '" href="#' +
            value.staffid +
            '" id="' +
            value.staffid +
            '" class="on ' +
            value.status +
            '"><span class="user-name onlineUsername">' +
            strCapitalize(value.firstname + " " + value.lastname) +
            '</span><img src="' +
            fetchUserAvatar(value.staffid, value.profile_image) +
            '" class="imgFriend" /></a>';
        } else {
          offlineUser +=
            '<a href="#' +
            value.staffid +
            '" id="' +
            value.staffid +
            '" class="off"';
          var lastLoginText = "";
          if (value.last_login) {
            lastLoginText = moment(
              value.last_login,
              "YYYYMMDD h:mm:ss"
            ).fromNow();
          } else {
            lastLoginText = (typeof prchatLang !== 'undefined' && prchatLang.lastSeenNever) ? prchatLang.lastSeenNever : "Never";
          }
          offlineUser +=
            ' data-toggle="tooltip" title="' +
            prchatSettings.chatLastSeenText +
            ": " +
            lastLoginText +
            '">';
          offlineUser +=
            '<span class="user-name">' +
            strCapitalize(value.firstname + " " + value.lastname) +
            '</span><img src="' +
            fetchUserAvatar(value.staffid, value.profile_image) +
            '" class="imgOther" /></a>';
        }
      }
    });
    $("#pusherChat #members-list").html("");
    $("#pusherChat #members-list").prepend(onlineUser + offlineUser);

    if (subscribed_event === true) {
      if (prchatSettings.getUnread != null) {
        var parsedUnreadMessages = JSON.parse(prchatSettings.getUnread);
        $.each(parsedUnreadMessages, function (i, sender) {
          insertId = $("#pusherChat #members-list a#" + sender.sender_id);
          if (sender.sender_id === $(insertId).attr("id")) {
            var messageCount = sender.count_messages || 0;
            notification =
              '<span class="unread-notifications" data-badge="' +
              messageCount +
              '"></span>';
            $(insertId).addClass("animated flash");
            $(notification).insertBefore(
              "#pusherChat #members-list a#" + sender.sender_id + " span"
            );
          }
        });
      }

      $.each(getActiveChatWindowsFromStorage(), function (index, obj) {
        var $userList = $("body").find(
          '#members-list a[href="#' + obj.id + '"]'
        );
        $userList.addClass("active-windows-click");
        $userList.click();
      });
    }
  });
}

/*---------------* Capitalize first string of letter *---------------*/
function strCapitalize(string) {
  if (string != undefined) {
    //  All unicode languages support
    var firstCP = string.codePointAt(0);
    var index = firstCP > 0xffff ? 2 : 1;

    return String.fromCodePoint(firstCP).toUpperCase() + string.slice(index);
  }
}

function updateLatestMessages(id) {
  $.post(prchatSettings.updateUnread, {
    id: id,
  }).done(function (r) {
    if (r != "true") {
      return false;
    }
  });
}

function fetchUserAvatar(id, image_name) {
  var type = "thumb";
  var url = site_url + "/assets/images/user-placeholder.jpg";

  // Check if id is undefined or null
  if (id == undefined || id == null || id === "") {
    return url;
  }

  if (
    image_name == false ||
    image_name == null ||
    image_name == undefined ||
    image_name === ""
  ) {
    return url;
  }

  if (image_name != null && image_name != undefined) {
    url =
      site_url +
      "/uploads/staff_profile_images/" +
      id +
      "/" +
      type +
      "_" +
      image_name;
  } else {
    url = site_url + "/assets/images/user-placeholder.jpg";
  }
  return url;
}

function prchat_setNoMoreMessages(to) {
  if ($("#no_messages_" + to).length == 0) {
    $(".logMsg#id_" + to).prepend(
      '<div class="text-center" style="margin-top:5px;" id="no_messages_' +
        to +
        '">' +
        prchatSettings.noMoreMessagesText +
        "</div>"
    );
  }
}

/*---------------* Sound functions - Updated to use Web Audio API *---------------*/
var isSoundMuted = "";

// Web Audio API sound functions (handled by ChatSoundManager.js)
function playChatSound() {
  if (typeof playNotificationBeep === "function") {
    playNotificationBeep();
    return true;
  }
  return false;
}

function appendUserSound() {
  if (typeof playNotificationBeep === "function") {
    playNotificationBeep();
    return true;
  }
  return false;
}

function userSeenNotify() {
  if (typeof playNotificationBeep === "function") {
    playNotificationBeep();
  }
}

var positions = JSON.parse(localStorage.positions || "{}");
var availableWidth = document.body.clientWidth - 305;
var availableHeight = document.body.clientHeight - 250;

const draggableOptions = {
  axis: "x,y",
  scroll: false,
  handle: "#membersContent .topInfo, #membersContent .chat-footer",

  start: function (event, ui) {
    $("#mainChatId").addClass("main-chat-dragging isToggled");
  },

  drag: function (event, ui) {
    const { top, left } = ui.position;
    const isWithinWidth = left > -availableWidth && left <= 0;
    const isWithinHeight = top > -availableHeight && top <= 0;

    ui.position.left = isWithinWidth ? left : -availableWidth;
    ui.position.top = isWithinHeight ? top : -availableHeight;

    positions[this.id] = ui.position;
    localStorage.positions = JSON.stringify(positions);
  },
};

$("#pusherChat .draggable").draggable(draggableOptions);

var scrollPosition = $("#pusherChat .scroll");
window.onload = function () {
  var localStoragePos = localStorage.chat_head_position;
  var localStorageisToggled = localStorage.isToggled;
  if (localStorage.isToggled == "true") {
    chatCircleTransform();
  }
  if (typeof localStoragePos != "undefined") {
    if (localStorageisToggled == "true") {
      localStorage.chat_head_position = "block";
      scrollPosition.css("display", "block");
      return;
    }
    scrollPosition.css("display", localStoragePos);
  } else {
    localStorage.chat_head_position = "block";
    scrollPosition.css("display", localStoragePos);
  }
};

function _debounce(func, wait, immediate) {
  var timeout;
  return function () {
    var context = this,
      args = arguments;
    var later = function () {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
}

function chatCircleTransform() {
  var inputColor = $(".colorHolder");
  var inputColorGradient = $("#colorGradientChanger");

  if (!$("#mainChatId").hasClass("main-chat-dragging")) {
    $(
      ".scroll, .chat-footer, .fa.fa-eercast, .chat-footer .online, .topInfo, #searchUsers, #disableSound, #colorChanger, #membersContent"
    ).toggleClass("isToggled");
    inputColor.is(":visible") ? inputColor.hide() : inputColor.show();
  }
  $(".toCircle").css({
    width: "30px",
    height: "30px",
    top: "11px",
    right: "9px",
  });
  if ($(".scroll").hasClass("isToggled")) {
    inputColorGradient.hide();
    localStorage.isToggled = "true";
    localStorage.chat_head_position = "none";
    scrollPosition.css("display", "none");
  } else {
    $(".toCircle").css({
      width: "23px",
      height: "23px",
      top: "unset",
      right: "36px",
    });
    inputColorGradient.show();
    scrollPosition.css("display", "none");
    localStorage.chat_head_position = "none";
    localStorage.isToggled = "false";
  }
}

function unescapeHtml(unsafe) {
  return unsafe
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'");
}

// Delete conversations
const deleteUrl = admin_url + "prchat/Prchat_Controller/purgeConversations";

function purgeStaffHistory() {
  if (askConfirmation() && promptAccepted()) {
    deleteChatConversation("staff");
  }
}

function purgeClientsHistory() {
  if (askConfirmation() && promptAccepted()) {
    deleteChatConversation("clients");
  }
}

function purgeGroupsHistory() {
  if (askConfirmation() && promptAccepted()) {
    deleteChatConversation("groups");
  }
}

function purgeAllHistory() {
  if (askConfirmation() && promptAccepted()) {
    deleteChatConversation("all");
  }
}

function purgeChatbotConversations() {
  if (askConfirmation() && promptAccepted()) {
    deleteChatConversation("chatbot");
  }
}

function promptAccepted() {
  let confirmation = prompt(prchatSettings.typeInToConfirm, "");

  confirmation = confirmation === "DELETE";

  if (!confirmation) alert_float('info', prchatSettings.actionCancelled);

  return confirmation;
}

function askConfirmation() {
  return confirm(prchatSettings.areYouSure);
}

function deleteChatConversation(type) {
  $.post(deleteUrl, { type: type }).done(function (r) {
    if (true === r.success)
      alert_float("success", prchatSettings.conversationDeleted);
    if (false === r.success)
      alert_float("info", prchatSettings.conversationAlreadyDeleted);
    if (r.error) alert_float("info", r.error);

    setTimeout(() => location.reload(), 2000);
  });
}
