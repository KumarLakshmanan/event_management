var Share = {
  facebook: (url, title) => {
    ShareUrl = "https://facebook.com/sharer/sharer.php?";
    ShareUrl += "u=" + encodeURIComponent(url);
    ShareUrl += `&quote=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    Share.popup(ShareUrl);
  },
  twitter: (url, title) => {
    ShareUrl = "https://twitter.com/intent/tweet/?";
    ShareUrl += `text=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    ShareUrl += "&url=" + encodeURIComponent(url);
    Share.popup(ShareUrl);
  },
  linkedin: (url, title) => {
    ShareUrl = "https://www.linkedin.com/shareArticle?mini=true&";
    ShareUrl += "url=" + encodeURIComponent(url);
    ShareUrl += `&title=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    encodeURIComponent(title);
    Share.popup(ShareUrl);
  },
  mail: (url, title) => {
    ShareUrl = "mailto://?";
    ShareUrl += "subject=" + encodeURIComponent(title);
    ShareUrl += `&body=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    Share.popup(ShareUrl);
  },
  pinterest: (url, title) => {
    ShareUrl = "http://pinterest.com/pin/create/button/?";
    ShareUrl += "url=" + encodeURIComponent(url);
    ShareUrl += "&media=" + encodeURIComponent(url);
    ShareUrl += `&description=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    Share.popup(ShareUrl);
  },
  whatsapp: (url, title) => {
    ShareUrl = "whatsapp://send?";
    ShareUrl += `text=Source Code for %0a %0a ${encodeURIComponent(
      title
    )} %0a ${encodeURIComponent(url)}`;
    Share.popup(ShareUrl);
  },
  reddit: (url, title) => {
    ShareUrl = "http://reddit.com/submit/?resubmit=true&";
    ShareUrl += `title=Source Code for%0a %0a ${encodeURIComponent(
      title
    )} %0a %0a ${encodeURIComponent(url)} `;
    ShareUrl += "&url=" + encodeURIComponent(url);
    Share.popup(ShareUrl);
  },
  telegram: (url, title) => {
    ShareUrl = "tg://msg_url?";
    ShareUrl += `text=Source Code for%0a %0a ${encodeURIComponent(
      title
    )} `;
    ShareUrl += "&url=" + encodeURIComponent(url);
    Share.popup(ShareUrl);
  },
  copy: (url, title) => {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val(`${title} \n\n\n ${url}`).select();
    document.execCommand("copy");
    alert("COPIED !!!");
    $temp.remove();
  },
  popup: function (url) {
    window.open(url, "", "toolbar=0,status=0,width=626, height=436");
  },
};
