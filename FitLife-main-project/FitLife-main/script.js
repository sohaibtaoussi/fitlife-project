console.log("Script loaded");

// Fonction pour afficher / masquer la sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const toggleBtn = document.querySelector('.toggle-btn');

    if (!sidebar || !overlay || !toggleBtn) {
        console.warn("Un ou plusieurs éléments de la sidebar sont manquants !");
        return;
    }

    sidebar.classList.toggle('open');
    overlay.style.display = sidebar.classList.contains('open') ? "block" : "none";
    const isOpen = sidebar.classList.contains('open');
    toggleBtn.setAttribute('aria-expanded', isOpen);
    sidebar.setAttribute('aria-hidden', !isOpen);
}

// Fermer la sidebar en cliquant sur l'overlay
document.addEventListener("DOMContentLoaded", function () {
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.remove('open');
            this.style.display = 'none';
        });
    } else {
        console.warn("⚠️ L'élément '.sidebar-overlay' est introuvable.");
    }
});

// Filtrer les produits (je ne modifie pas cette partie pour l'instant)
function filterProducts(selectedSports, selectedBrands) {
    console.log("filterProducts called with:", selectedSports, selectedBrands);
    const products = document.querySelectorAll('.pro');
    const spinner = document.getElementById('loading-spinner');
    if (!spinner) {
        console.warn("⚠️ L'élément '#loading-spinner' est introuvable.");
        return;
    }
    spinner.style.display = 'block';
    setTimeout(() => {
        products.forEach((product) => {
            const productSport = product.getAttribute('data-sport');
            const productBrand = product.getAttribute('data-brand');
            const sportMatch = selectedSports.length === 0 || selectedSports.includes(productSport);
            const brandMatch = selectedBrands.length === 0 || selectedBrands.includes(productBrand);
            product.style.display = sportMatch && brandMatch ? 'block' : 'none';
        });
        spinner.style.display = 'none';
    }, 500);
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('input[name="sport"], input[name="brand"]').forEach(input => {
        input.addEventListener('change', updateSelections);
    });

    console.log("DOM fully loaded and parsed");
    updateSelections();
});

// Récupérer les paramètres de l'URL
const urlParams = new URLSearchParams(window.location.search);
const productName = urlParams.get('name');
const productPrice = urlParams.get('price');
const productImage = urlParams.get('image');
const productBrand = urlParams.get('brand');

// Vérifier et afficher les informations du produit
document.addEventListener("DOMContentLoaded", function () {
    const productImageElement = document.getElementById('product-image');
    const productTitleElement = document.getElementById('product-title');
    const productPriceElement = document.getElementById('product-price');
    const productBrandElement = document.getElementById('product-brand');

    if (productImageElement && productImage) {
        productImageElement.src = decodeURIComponent(productImage);
    } else {
        console.error('Product image URL is missing or element not found.');
    }

    if (productTitleElement && productName) {
        productTitleElement.textContent = productName;
    } else {
        console.error('Product name is missing or element not found.');
    }

    if (productPriceElement && productPrice) {
        productPriceElement.textContent = `${productPrice} MAD`;
    } else {
        console.error('Product price is missing or element not found.');
    }

    if (productBrandElement && productBrand) {
        productBrandElement.textContent = productBrand;
    } else {
        console.error('Product brand is missing or element not found.');
    }
});

// Function to add product to the cart (this will handle the POST request)
function addToCart(productId, productName, productPrice) {
    // Send POST request to add product to cart
    fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${productId}&name=${encodeURIComponent(productName)}&price=${encodeURIComponent(productPrice)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "Product added!",
                text: "You will be redirected to your cart.",
                icon: "success",
                confirmButtonText: "Go to Cart",
            }).then(() => {
                window.location.href = "cart.php"; // Redirect to cart
            });
        } else {
            Swal.fire("Error", data.message || "Couldn't add product to cart", "error");
        }
    })
    .catch(error => console.error("Fetch error:", error));
}

// Add event listeners to all "Add to Cart" buttons on the shop page
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();

        const productId = this.getAttribute('data-id');
        const productName = this.getAttribute('data-name');
        const productPrice = this.getAttribute('data-price');

        addToCart(productId, productName, productPrice);
    });
});

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById("addToCartModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
