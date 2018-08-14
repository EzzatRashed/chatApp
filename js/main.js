/* Functions */

function searchContacts(){
	var input, filter, ul, li, name, i;
	var e = 0;
    input = document.getElementById('search_input');
    filter = input.value.toUpperCase();
    ul = document.getElementById("contacts");
    li = ul.getElementsByTagName('li');
    for (i = 0; i < li.length; i++) {
        name = li[i].getElementsByTagName("div")[0].getElementsByTagName("h4")[0];
        if (name.innerHTML.toUpperCase().indexOf(filter) > -1) {
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
    }else if (search_text != null){
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
		}
	};
	xhttp.open("POST", "chatAPI", true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send("load_chat=" + name);
}

/* Functions End Here */

// This block fires when the DOM is loaded
document.addEventListener("DOMContentLoaded", function(event) { 
	loadSideListHTML();
	document.getElementsByClassName('loader')[0].setAttribute("style","display:none;");
	document.getElementsByClassName('loader')[1].setAttribute("style","display:none;");
});