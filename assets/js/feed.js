Miniflux.Feed = (function() {

    // List of subscriptions
    var feeds = [];

    // List of feeds currently updating
    var queue = [];

    // Number of concurrent requests when updating all feeds
    var queue_length = 5;

    return {
        Update: function(feed, callback) {
            var itemsCounter = feed.querySelector("span.items-count");
            if (! itemsCounter) return;

            var feed_id = feed.getAttribute("data-feed-id")

            var heading = feed.querySelector("h2:first-of-type");
            heading.className = "loading-icon";

            var request = new XMLHttpRequest();
            request.onload = function() {
                heading.className = "";
                feed.removeAttribute("data-feed-error");

                var lastChecked = feed.querySelector(".feed-last-checked");
                if (lastChecked) lastChecked.innerHTML = lastChecked.getAttribute("data-after-update");

                var response = JSON.parse(this.responseText);
                if (response['result']) {
                    itemsCounter.innerHTML = response['items_count']['items_unread'] + "/" + response['items_count']['items_total'];
                } else {
                    feed.setAttribute("data-feed-error", "1");
                }

                if (callback) {
                    callback(response);
                }
                else {
                    Miniflux.Item.CheckForUpdates();
                }
            };

            request.open("POST", "?action=refresh-feed&feed_id=" + feed_id, true);
            request.send();
        },
        UpdateAll: function() {
            var feeds = Array.prototype.slice.call(document.querySelectorAll("article:not([data-feed-disabled])"));

            var interval = setInterval(function() {
                while (feeds.length > 0 && queue.length < queue_length) {
                    var feed = feeds.shift();
                    queue.push(parseInt(feed.getAttribute('data-feed-id'), 10));

                    Miniflux.Feed.Update(feed, function(response) {
                        var index = queue.indexOf(response['feed_id']);
                        if (index >= 0) queue.splice(index, 1);

                        if (feeds.length === 0 && queue.length === 0) {
                            clearInterval(interval);
                            Miniflux.Item.CheckForUpdates();
                        }
                    });
                }
            }, 100);
        },
        ToggleTag: function (tag) {
            var el = tag.srcElement, val = el.getAttribute('data-value');
            if (el.className.indexOf('active') > -1) {
                el.classList.remove('active');
                document.getElementById('form-' + el.id).value = '';
            } else {
                el.classList.add('active');
                document.getElementById('form-' + el.id).value = val;
            }
        },
        AddTag: function (tag) {
            var tagTitle = prompt("Please enter the title for the new tag", "News");

            if (tagTitle) {
                var request = new XMLHttpRequest(), params = 'title=' + tagTitle;
                request.onload = function () {
                    if(this.responseText){
                        document.getElementById('tags').insertAdjacentHTML('beforeend', this.responseText);
                    }
                };

                request.open("POST", "?action=tag-create", true);
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.send(params);
            }
        }
    };
})();