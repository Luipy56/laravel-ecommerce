const modalClose = document.getElementById("modal-close");
if (modalClose !== null) {
  modalClose.addEventListener("click", () => {
    const infoModal = document.getElementById("vacances-modal");
    const backdrop_modal = document.getElementsByClassName("modal-backdrop");
	backdrop_modal[0].classList.remove('show');
    infoModal.classList.add("d-none");
	backdrop_modal[0].parentNode.removeChild(backdrop_modal[0]);
    console.log(backdrop_modal);
  });
}
