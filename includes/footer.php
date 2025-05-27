<!-- Footer Start -->
<div class="container-fluid bg-secondary text-dark mt-5 pt-5">
    <div class="row px-xl-5 pt-5">
        <div class="col-lg-4 col-md-12 mb-5 pr-3 pr-xl-5">
            <a href="" class="text-decoration-none">
                <h1 class="mb-4 display-5 font-weight-semi-bold"><span class="text-primary font-weight-bold border border-white px-3 mr-1">E</span>Shopper</h1>
            </a>
            <p>Loja virtual com os melhores produtos e preços do mercado.</p>
            <p class="mb-2"><i class="fa fa-map-marker-alt text-primary mr-3"></i>Rua Exemplo, 123, São Paulo, SP</p>
            <p class="mb-2"><i class="fa fa-envelope text-primary mr-3"></i>contato@eshopper.com</p>
            <p class="mb-0"><i class="fa fa-phone-alt text-primary mr-3"></i>+55 11 99999-9999</p>
        </div>
        <div class="col-lg-8 col-md-12">
            <div class="row">
                <div class="col-md-4 mb-5">
                    <h5 class="font-weight-bold text-dark mb-4">Links Rápidos</h5>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-dark mb-2" href="index.php"><i class="fa fa-angle-right mr-2"></i>Home</a>
                        <a class="text-dark mb-2" href="shop.php"><i class="fa fa-angle-right mr-2"></i>Nossa Loja</a>
                        <a class="text-dark mb-2" href="cart.php"><i class="fa fa-angle-right mr-2"></i>Carrinho</a>
                        <a class="text-dark mb-2" href="checkout.php"><i class="fa fa-angle-right mr-2"></i>Checkout</a>
                        <a class="text-dark mb-2" href="contact.php"><i class="fa fa-angle-right mr-2"></i>Contato</a>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <h5 class="font-weight-bold text-dark mb-4">Categorias</h5>
                    <div class="d-flex flex-column justify-content-start">
                        <?php
                        $categories = $conn->query("SELECT * FROM categorias ORDER BY nome");
                        if ($categories && $categories->num_rows > 0) {
                            while($category = $categories->fetch_assoc()) {
                        ?>
                        <a class="text-dark mb-2" href="shop.php?categoria=<?php echo $category['id']; ?>">
                            <i class="fa fa-angle-right mr-2"></i><?php echo htmlspecialchars($category['nome']); ?>
                        </a>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <h5 class="font-weight-bold text-dark mb-4">Newsletter</h5>
                    <form action="">
                        <div class="form-group">
                            <input type="text" class="form-control border-0 py-4" placeholder="Seu Nome" required="required" />
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control border-0 py-4" placeholder="Seu Email" required="required" />
                        </div>
                        <div>
                            <button class="btn btn-primary btn-block border-0 py-3" type="submit">Inscrever-se</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row border-top border-light mx-xl-5 py-4">
        <div class="col-md-6 px-xl-0">
            <p class="mb-md-0 text-center text-md-left text-dark">
                &copy; <a class="text-dark font-weight-semi-bold" href="#">EShopper</a>. Todos os direitos reservados.
            </p>
        </div>
        <div class="col-md-6 px-xl-0 text-center text-md-right">
            <img class="img-fluid" src="img/payments.png" alt="">
        </div>
    </div>
</div>
<!-- Footer End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
<script src="lib/easing/easing.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Template Javascript -->
<script src="js/main.js"></script> 