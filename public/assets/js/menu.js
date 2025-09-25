/**
 * Exemple de données de menu.
 * Chaque élément possède :
 *  - id : identifiant
 *  - label : texte affiché
 *  - icon : URL vers une image PNG (remplace par tes PNG)
 *  - subtitle : (optionnel) petit texte descriptif
 */
const menuItems = [
    {
        id: "tdb",
        label: "Tableau de bord",
        icon: "assets/icons/data-analysis.png",
    },
    {
        id: "reports",
        label: "Rapports",
        icon: "assets/icons/document.png",
    },
    {
        id: "serv",
        label: "Serveurs",
        icon: "assets/icons/serving-dish.png",
    },
    {
        id: "prod",
        label: "Produits",
        icon: "assets/icons/add-product.png",
    },
    {
        id: "empl",
        label: "Emplacements",
        icon: "assets/icons/home-button.png",
    },
    {
        id: "facs",
        label: "Factures",
        icon: "assets/icons/quality-control.png",
    },
    {
        id: "sell",
        label: "Ventes",
        icon: "assets/icons/online-shopping.png",
    },
    {
        id: "cmd",
        label: "Commandes",
        icon: "assets/icons/room-service.png",
    },
    {
        id: "chambres",
        label: "Chambres",
        icon: "assets/icons/hotel-check-in.png",
    },
    {
        id: "users",
        label: "Utilisateurs",
        icon: "assets/icons/user.png",
    },
];

// Référence au conteneur
const grid = document.getElementById("menuGrid");

// Génére les boutons et les attache au DOM
menuItems.forEach((item) => {
    const btn = document.createElement("button");
    btn.className = "menu-btn";
    btn.setAttribute("type", "button");
    btn.setAttribute("data-id", item.id);
    btn.setAttribute("aria-pressed", "false");
    btn.setAttribute("title", item.label);

    // image icone (PNG recommandé)
    const img = document.createElement("img");
    img.className = "menu-icon";
    img.alt = item.label + " icon";
    img.src = item.icon; // remplace par ton PNG
    // évite que la casse src casse le rendu si l'image n'existe pas
    img.onerror = function () {
        this.style.visibility = "hidden";
    };

    // label
    const lbl = document.createElement("div");
    lbl.className = "menu-label";
    lbl.textContent = item.label;

    btn.appendChild(img);
    btn.appendChild(lbl);

    // si subtitle existe, on peut l'ajouter en plus (optionnel)
    if (item.subtitle) {
        const sub = document.createElement("div");
        sub.className = "menu-sub";
        sub.textContent = item.subtitle;
        btn.appendChild(sub);
    }

    // événement de sélection
    btn.addEventListener("click", () => toggleActive(btn, item));
    btn.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            toggleActive(btn, item);
        }
    });

    grid.appendChild(btn);
});

// Gère l'état actif / selection
function toggleActive(button, item) {
    // si déjà actif, on le désactive
    const isActive = button.classList.contains("active");
    // désactiver tous
    document.querySelectorAll(".menu-btn").forEach((b) => {
        b.classList.remove("active");
        b.setAttribute("aria-pressed", "false");
    });
    if (!isActive) {
        button.classList.add("active");
        button.setAttribute("aria-pressed", "true");
        // Ici tu peux appeler une fonction pour naviguer / ouvrir une page / lancer une action
        // Exemple : openMenu(item.id)
        console.log("Ouvrir menu :", item.id);
    } else {
        // si on cliquait pour désélectionner
        console.log("Désélection :", item.id);
    }
}

// Exemple : fonction pour remplacer dynamiquement les icônes (utilitaire)
function updateIcon(id, newUrl) {
    const btn = document.querySelector(`.menu-btn[data-id="${id}"]`);
    if (!btn) return;
    const img = btn.querySelector(".menu-icon");
    if (img) {
        img.src = newUrl;
        img.style.visibility = "visible";
    }
}

// === Fonction pour ajouter un badge ===
function setBadge(id, count) {
    const btn = document.querySelector(`.menu-btn[data-id="${id}"]`);
    if (!btn) return;
    let badge = btn.querySelector(".badge");
    if (!badge) {
        badge = document.createElement("span");
        badge.className = "badge";
        btn.appendChild(badge);
    }
    badge.textContent = count > 99 ? "99+" : count;
}

// Exemple : badge sur "reservations"
setBadge("facs", 5);

// NOTE: remplace les URLs 'https://via.placeholder...' par tes fichiers locaux :
// ex: '/assets/assets/icons/entrees.png' (format PNG recommandé).
