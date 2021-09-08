(function () {
    "use strict";

    // modal
    tutorModal();



})()


function tutorModal() {

    document.addEventListener("click", (e) => {
        const attr = "data-tutor-modal-target";
        const closeAttr = "data-tutor-modal-close";
        const overlay = "tutor-modal-overlay";

        if (e.target.hasAttribute(attr) || e.target.closest(`[${attr}]`)) {
            e.preventDefault();
            const id = e.target.hasAttribute(attr) ? e.target.getAttribute(attr) :  e.target.closest(`[${attr}]`).getAttribute(attr);
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add("tutor-is-active");
            }
        }

        if (
            e.target.hasAttribute(closeAttr) ||
            e.target.classList.contains(overlay) ||
            e.target.closest(`[${closeAttr}]`)
        ) {
            e.preventDefault();
            const modal = document.querySelectorAll(".tutor-modal.tutor-is-active");
            modal.forEach(m => {
                m.classList.remove("tutor-is-active");
            })
        }


    })

    // open
    // const modalButton = document.querySelectorAll("[data-tutor-modal-target]");
    // modalButton.forEach(b => {
    //     const id = b.getAttribute("data-tutor-modal-target");
    //     const modal = document.getElementById(id);
    //     if (modal) {
    //         b.addEventListener("click", e => {
    //             e.preventDefault();
    //             modal.classList.add("tutor-is-active");
    //         })
    //     }
    // })

    // close
    // const close = document.querySelectorAll("[data-tutor-modal-close], .tutor-modal-overlay");
    // close.forEach(c => {
    //     c.addEventListener("click", e => {
    //         e.preventDefault();
    //         const modal = document.querySelectorAll(".tutor-modal.tutor-is-active");
    //         modal.forEach(m => {
    //             m.classList.remove("tutor-is-active");
    //         })
    //     })
    // })
}