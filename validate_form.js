(function () {
	var submit = document.getElementById("adduserbutton");
	submit.addEventListener("click", function(event){
		var new_username = document.getElementById("new_username");
		var pass1 = document.getElementById("pass1");
		var pass2 = document.getElementById("pass2");
		if (new_username.value && pass1.value && pass2.value) {
		  if (pass1.value != pass2.value) {
		  	event.preventDefault();
		  	alert("Passwords do not match");
		  }
		}
		else {
			event.preventDefault();
			alert("Fill in all the fields in the form.");
		}
	});
})();
