var form = document.querySelector('form');
var actionInputContainer = document.querySelector(".action_input_container");

actionInputContainer.addEventListener('click', function (event) {
	var imgInput = document.querySelector("input[name=image]");
		if (imgInput) {
			if (!imgInput.value) {
				imgInput.parentNode.removeChild(imgInput);
			}
		}
});