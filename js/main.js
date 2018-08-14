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

function loadContact(name) {
	document.getElementById('chat').innerHTML = '<div class="loader"><div></div></div>';
  	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
	if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
	 		document.getElementById('chat').innerHTML = this.responseText;
	 		send_textarea();
		}
	};
	xhttp.open("POST", "chatAPI", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("load_chat=" + name);
}

function send_textarea(){
    var message_to_send = document.getElementById("message_to_send");
    message_to_send.addEventListener("keypress", function(event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            document.getElementById("send_btn").click();
        }
    });
}

function send_message(a){
	var message_to_send = document.getElementById("message_to_send");
	var text_to_send = message_to_send.value;
    message_to_send.value = '';
	if (text_to_send.trim() != '') {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
                document.getElementById('chat_list').insertAdjacentHTML('beforeend', this.responseText);
                var target = document.getElementById('chat_list').lastChild;
                target.parentNode.scrollTop = target.offsetTop;
                if (document.body.contains(document.getElementById('empty')) && document.getElementById('empty').getAttribute("style") != "display:none;") {
                    document.getElementById('empty').setAttribute("style","display:none;");
                }
            }
        };
        xhttp.open("POST", "chatAPI", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("msg=" + text_to_send + "&to=" + a);
	}
}

/* Functions End Here */

// This block fires when the DOM is loaded
document.addEventListener("DOMContentLoaded", function(event) { 
	loadSideListHTML();
	document.getElementsByClassName('loader')[0].setAttribute("style","display:none;");
	document.getElementsByClassName('loader')[1].setAttribute("style","display:none;");
});