var imageInput = document.querySelector("input[name='image']");

imageInput.addEventListener('change', preview_image);

function preview_image(event) {
	var output = document.querySelector('#output_image');
	var header = document.querySelector('.preview_header');
	output.style.display = "none";
	header.style.display = "none";
	var reader = new FileReader();
	reader.onload = function() {
		output.style.backgroundImage = "url(" + reader.result + ")";
		output.style.display = "block";
		header.style.display = "block";
	}
reader.readAsDataURL(event.target.files[0]);
}