/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your components layout (components.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import 'flowbite';

function previewImage() {
    console.log("HERE")
    const file = document.getElementById("profilePhoto").files[0];
    const label = document.getElementById("profilePhotoLabel");
    const reader = new FileReader();

    reader.onloadend = function() {
        label.innerHTML = "";
        label.style.backgroundImage = "url(" + reader.result + ")";

    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        label.style.backgroundImage = "none";
        label.innerHTML =
            "                  <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\" class=\"w-8 h-8\">\n" +
            "                                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z\" />\n" +
            "                                </svg>";
    }
}

global.previewImage = previewImage;