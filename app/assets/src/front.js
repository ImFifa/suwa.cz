import "@theme/front/init.scss";

import "bootstrap/js/dist/dropdown";
import "bootstrap/js/dist/collapse";
import "bootstrap/js/dist/modal";
import "bootstrap/js/dist/util";

import "lightbox2/dist/css/lightbox.css";
// eslint-disable-next-line no-unused-vars
import lightbox from "lightbox2/dist/js/lightbox";

import Nette from "@/front/netteForms";
Nette.initOnLoad();
window.Nette = Nette;


const $scrollTopBtn = document.querySelector("#scrollTopBtn");

function runOnScroll() {
	var currentScrollPos = window.pageYOffset;

	if (window.innerWidth < 576) {
		if (currentScrollPos > window.innerHeight) {
			$scrollTopBtn.style.display = "flex";
		} else {
			$scrollTopBtn.style.display = "none";
		}
	} else {
		if (currentScrollPos > window.innerHeight) {
			$scrollTopBtn.style.right = "0";
		} else {
			$scrollTopBtn.style.right = "-170px";
		}
	}

}

document.addEventListener("DOMContentLoaded", () => {
	// modal after registration
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	var odeslano = urlParams.get("odeslano");
	if(odeslano){
		$("#mailSentModal").modal("show");
		console.log("modal is shown");
	}

	// on scroll events
	// eslint-disable-next-line no-undef
	runOnScroll();
	// eslint-disable-next-line no-undef
	window.addEventListener("scroll", runOnScroll);

	// close navbar on link click
	$("#navbar .scroll").click(function(){
		$(".navbar-collapse").collapse("hide");
	});
});
