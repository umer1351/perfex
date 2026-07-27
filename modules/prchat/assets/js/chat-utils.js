/**
 * Shared chat utility functions used across admin and client views.
 * Extracted from pr-chat.js to avoid jQuery UI dependencies on client side.
 */
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
        return string + '<a href="' + hyperlink + '" target="_blank"><img style="width:100%;height:100%;padding-top:2px;" rel="nofollow" src="' + hyperlink + '"/></a>';
      } else if (isVideoUrl(hyperlink)) {
        return string + '<a href="' + hyperlink + '" target="_blank" data-video-embed="true" rel="nofollow">' + url + '</a>';
      } else {
        return string + '<a target="_blank" rel="nofollow" href="' + hyperlink + '">' + url + '</a>';
      }
    }
  );
}
