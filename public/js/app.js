document.addEventListener("DOMContentLoaded", () => {
    const menu = document.querySelector("[data-menu]");
    const sidebar = document.getElementById("sidebar");
    const closeMenu = () => {
        document.body.classList.remove("menu-open");
        menu?.setAttribute("aria-expanded", "false");
    };
    menu?.addEventListener("click", () => {
        const open = document.body.classList.toggle("menu-open");
        menu.setAttribute("aria-expanded", String(open));
        if (open) sidebar.querySelector("a")?.focus();
    });
    document
        .querySelector("[data-close-menu]")
        ?.addEventListener("click", closeMenu);
    document.addEventListener("keydown", (event) => {
        if (!document.body.classList.contains("menu-open")) return;
        if (event.key === "Escape") {
            closeMenu();
            menu.focus();
        }
        if (event.key === "Tab") {
            const items = sidebar.querySelectorAll("a, button");
            const first = items[0],
                last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    window
        .matchMedia("(min-width: 1001px)")
        .addEventListener("change", (event) => {
            if (event.matches) closeMenu();
        });
    document.querySelectorAll("[data-password]").forEach((button) =>
        button.addEventListener("click", () => {
            const input = document.getElementById(button.dataset.password);
            const show = input.type === "password";
            input.type = show ? "text" : "password";
            button.textContent = show ? "Ocultar" : "Mostrar";
            button.setAttribute(
                "aria-label",
                show ? "Ocultar senha" : "Mostrar senha",
            );
        }),
    );
});
