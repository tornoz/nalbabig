/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

document.addEventListener("DOMContentLoaded", (event) => {
    var searchBar = document.getElementById('search');
    var anviou = document.getElementsByClassName("anv")
    searchBar.addEventListener('input', (event) => {
        var term = event.target.value;
        Array.from(anviou).forEach((element) => {
            var listElement = element.parentNode.parentNode;
            if(element.textContent.includes(term)) {
                listElement.style.display = 'inline-block';
            } else {
                listElement.style.display = 'none';
            }
        });

    });
});
