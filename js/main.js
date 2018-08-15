var chat_name = '';

/* Functions */

function searchContacts(){
	var input, filter, ul, li, name, i;
	var e = 0;
    input = document.getElementById('search_input');
    filter = input.value.toUpperCase();
    ul = document.getElementById("contacts");
    li = ul.getElementsByTagName('li');
    for (i = 0; i < li.length; i++) {
        name = li[i].dataset.name;
        if (name.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "block";
            e++;
        } else {
            li[i].style.display = "none";
        }
    }
    search_text = document.getElementById('search_text');
    if (filter != '' && li.length != 0 && e == 0 && search_text == null){
    	var search_text = '<h2 id="search_text" class="default_h2">Nobody is Found!</h2>';
    	document.getElementById('contacts').insertAdjacentHTML('beforeend', search_text);
    }else if (search_text != null && filter == ''){
    	search_text.parentNode.removeChild(search_text);
    }
}

// Close Emoji Menu When Click & Inserts Emojis into Textarea
document.documentElement.onclick = function(e) {
	var evt = e;
    var target = evt.target;
    var emojis_menu = document.getElementById('emojis_menu');
    if (target.id !== "emojis_button" && target.id !== "emojis_menu" && !target.classList.contains('emojis_menu_element') && emojis_menu != null) {
       	emojis_menu.style.display = 'none';
    }
    if (target.classList.contains('emojis_menu_element')){
    	document.getElementById('message_to_send').value += target.innerHTML;
    }
}


// API function 1
function loadSideListHTML() {
	var HTML_Response;
  	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
			HTML_Response = this.responseText;
			HTML_Array = HTML_Response.split('/n/r');
	 		document.getElementById('contacts').innerHTML = HTML_Array[0];
	 		document.getElementById('my_inbox').innerHTML = HTML_Array[1];
		}
	};
	xhttp.open("POST", "chatAPI", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("load_side_list=true");
}

// API function 2
function sendTextarea(){
    var message_to_send = document.getElementById("message_to_send");
    message_to_send.addEventListener("keypress", function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            document.getElementById("send_btn").click();
        }
    });
}

function emojisMenu() {
	var emojis_button = document.getElementById("emojis_button");
	var emojis_menu = document.getElementById("emojis_menu");
	if (emojis_menu.style.display == 'block') {
		emojis_menu.style.display = 'none';
	} else {
		emojis_menu.style.display = 'block';
	}
}

function loadContact(name) {
	document.getElementById('chat').innerHTML = '<div class="loader"><div></div></div>';
  	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
	 		document.getElementById('chat').innerHTML = this.responseText;
	 		sendTextarea();
	 		var target = document.getElementById('chat_list').lastElementChild;
            target.parentNode.scrollTop = target.offsetTop;
		}
	};
	xhttp.open("POST", "chatAPI", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("load_chat=" + name);
	chat_name = name;
}

// API function 3
function sendMessage(){
	var message_to_send = document.getElementById("message_to_send");
	var text_to_send = message_to_send.value;
    message_to_send.value = '';
	if (text_to_send.trim() != '') {
		// Change Emojis to Span Element
		var regex = /(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|[\ud83c[\ude01\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|[\ud83c[\ude32\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|[\ud83c[\ude50\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g;
		var text_to_send = text_to_send.replace(regex, function (x) {
		    return '<span>' + x + '</span>';
		});
		
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
                document.getElementById('chat_list').insertAdjacentHTML('beforeend', this.responseText);
                var target = document.getElementById('chat_list').lastElementChild;
                target.parentNode.scrollTop = target.offsetTop;
                if (document.body.contains(document.getElementById('empty')) && document.getElementById('empty').getAttribute("style") != "display:none;") {
                    document.getElementById('empty').setAttribute("style","display:none;");
                }
            }
        };
        xhttp.open("POST", "chatAPI", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("msg=" + text_to_send + "&to=" + chat_name);
	}
}

// API function 4
function getLastMsg(){
	if (chat_name != '') {
		var xhttp = new XMLHttpRequest();
	    xhttp.onreadystatechange = function() {
	    if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
	            if (document.body.contains(document.getElementById('empty')) && document.getElementById('empty').getAttribute("style") != "display:none;") {
	                document.getElementById('empty').setAttribute("style","display:none;");
	            }
	            document.getElementById('chat_list').insertAdjacentHTML('beforeend', this.responseText);
	            var target = document.getElementById('chat_list').lastElementChild;
	            target.parentNode.scrollTop = target.offsetTop;
	            new Audio('sound/new_msg.mp3').play()
	        }
	    };
	    xhttp.open("POST", "chatAPI", true);
	    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	    xhttp.send("get_msg_from=" + chat_name);
	}    
}

// API function 5
function updateChatbox(){
    loadSideListHTML();
    if (chat_name != '') {
	    var xhttp = new XMLHttpRequest();
	    xhttp.onreadystatechange = function() {
	    if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
	            var response = this.responseText;
	            response = response.split(',');
	            response.splice(-1,1);
	            var msgs = document.getElementsByClassName('msg_data_time');
	            var num_msgs = msgs.length;
	            response = response.splice(-num_msgs,num_msgs);
	            for (var i = 0; i <= num_msgs - 1; i++) {
	                document.getElementsByClassName('msg_data_time')[i].innerHTML = response[i];
	            }
	        }
	    };
	    xhttp.open("POST", "chatAPI", true);
	    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	    xhttp.send("get_data_time=" + chat_name);
	}
}

/* Functions End Here */

// This block fires when the DOM is loaded
document.addEventListener("DOMContentLoaded", function(event) { 
	loadSideListHTML();
	document.getElementsByClassName('loader')[0].setAttribute("style","display:none;");
	document.getElementsByClassName('loader')[1].setAttribute("style","display:none;");
});


// Periodic Functions
setInterval(getLastMsg, 2000); 
setInterval(updateChatbox, 5000); 