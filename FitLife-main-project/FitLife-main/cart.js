document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent unwanted default behavior
            
            const productId = this.getAttribute("data-id");
            const productName = encodeURIComponent(this.getAttribute("data-name"));
            const productPrice = encodeURIComponent(this.getAttribute("data-price"));

            console.log("Adding product:", productId, productName, productPrice); // Debug log

            fetch("add_to_cart.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${productId}&name=${productName}&price=${productPrice}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Product added successfully!"); // Debug log

                    Swal.fire({
                        title: "Produit ajouté !",
                        text: "Voulez-vous voir votre panier ou continuer vos achats ?",
                        icon: "success",
                        showCancelButton: true,
                        confirmButtonText: "Voir le panier",
                        cancelButtonText: "Continuer",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "cart.php"; // Redirect to cart
                        }
                    });

                    updateCartCount(); // Refresh cart count in the navbar
                } else {
                    console.error("Error:", data.message);
                    Swal.fire("Erreur", data.message || "Impossible d'ajouter le produit.", "error");
                }
            })
            .catch(error => console.error("Fetch error:", error));
        });
    });
});

// Function to update cart count in navbar
function updateCartCount() {
    fetch('cart_count.php')
    .then(response => response.text())
    .then(count => {
        console.log("Updated cart count:", count); // Debug log
        document.getElementById('cart-count').innerText = count;
    })
    .catch(error => console.error("Error updating cart count:", error));
}
