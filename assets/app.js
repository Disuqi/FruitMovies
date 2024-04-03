/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your components layout (components.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'flowbite';
const $ = require('jquery');
global.$ = global.jquery = $;

function previewImage(fileElementId)
{
    const file = document.getElementById(fileElementId).files[0];
    const label = document.getElementById("fileInputLabel");
    const noImageElement = document.getElementById("no-image-svg");
    const reader = new FileReader();

    reader.onloadend = () => {
        noImageElement.style.display = "none"
        label.style.backgroundImage = "url(" + reader.result + ")";
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        label.style.backgroundImage = "none";
        noImageElement.style.display = "block";
    }
}

global.previewImage = previewImage;