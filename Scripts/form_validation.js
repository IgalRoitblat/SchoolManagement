var form = document.querySelector('form');
var nameInput = form.querySelector("input[name=name]");
var phoneInput = form.querySelector("input[name=phone]");
var passwordInput = form.querySelector("input[type=password]");
var fileInput = form.querySelector("input[type=file]");

form.addEventListener('submit', onFormSubmit);

nameInput.addEventListener('change', removeErrorMsg);

if (phoneInput) {
	phoneInput.addEventListener('change', removeErrorMsg);
}

if (passwordInput) {
	passwordInput.addEventListener('change', removeErrorMsg);
}

if (fileInput) {
	fileInput.addEventListener('change', removeErrorMsg);
}

function submitForm(event) {
	form.submit();
}

function onFormSubmit(event) {
	if (nameInput.value.length < 3) {
		event.preventDefault();
		createErrorMsg(nameInput);
		nameInput.parentNode.querySelector('.errorMsg').textContent = 'Please enter a valid value';
	}

	if (phoneInput) {
		if (phoneInput.value.length < 9 || isNaN(phoneInput.value)) {
			event.preventDefault();
			createErrorMsg(phoneInput);
			phoneInput.parentNode.querySelector('.errorMsg').textContent = 'Please enter a valid phone';
		}
	}

	if (fileInput.value) {
		if (fileInput.files[0].size > 1000000) {
			event.preventDefault();
			createErrorMsg(fileInput);
			fileInput.parentNode.querySelector('.errorMsg').textContent = 'The image is too large';
		}
	}

	if (passwordInput) {
		if (passwordInput.value.length < 5) {
			event.preventDefault();
			createErrorMsg(passwordInput);
			passwordInput.parentNode.querySelector('.errorMsg').textContent = 'Password must contain at least 5 charecters';
		}
	}
}

function createErrorMsg(location) {
	var label = location.parentNode;
	var error = document.createElement('div');
	label.append(error);
	error.classList.add('errorMsg');
}

function removeErrorMsg(event) {
	msg = event.target.parentNode.querySelector('.errorMsg');
	if (msg) {
		msg.parentNode.removeChild(msg);
	}
}
