    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Campus Starts -->
                <section id="subcategories">

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Ficha del producto</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a>
                                            </li>
                                            <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header table-->

                    <div class="card p-3">
                        <div class="row">
                            <h2 class="mb-1">Productos</h2>
                            <div class="mb-1 col-10">
                                <form method="GET" enctype="multipart/form-data" id="select_product_form" class="row" onsubmit="return false">
                                    <div class="mb-1 col-10 position-relative" style="display: block;">
                                        <select name="id_section" class="select2" id="select-product" data-msg="" required>
                                        </select>
                                    </div>
                                    <div class="mb-1 col-2 text-end">
                                        <button class="btn btn-primary" name="btn_select_product" id="btn_select_product">Seleccionar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Create -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card p-3">
                                <h1 class="mb-2 text-center text-muted">FICHA</h1>
                                <form method="POST" enctype="multipart/form-data" id="create_product_form" class="mb-2 row" onsubmit="return false">
                                    <h2 class="card-title">Producto</h2>
                                    <div class="mb-1 col-6">
                                        <label class="form-label">Código</label>
                                        <input type="text" name="code" class="form-control" placeholder="Código" autofocus data-msg="" required />
                                    </div>
                                    <div class="mb-1 col-6">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="name" class="form-control" placeholder="Nombre" autofocus data-msg="" required />
                                    </div>
                                    <div class="mb-1 col-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" id="text-description" class="form-control" placeholder="Descripción" rows="3" maxlength="350" style="resize: none;"></textarea>
                                    </div>
                                    <div class="mb-1 col-4 position-relative">
                                        <div>
                                            <label class="form-label">Unidad de Medida</label>
                                            <select name="id_u_medida" class="form-select select2" id="select-medida-create" data-msg="" required>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-1 col-4 position-relative">
                                        <div>
                                            <label class="form-label">Etiqueta</label>
                                            <select name="id_label" class="form-select select2" id="select-label-create" data-msg="">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-1 col-4">
                                        <label class="form-label">Precio</label>
                                        <input type="number" name="price" class="form-control" placeholder="00.00" autofocus data-msg="" readonly />
                                    </div>
                                    <input type="hidden" name="id_product">
                                    <!--  -->
                                    <h2 class="card-title mt-3">Inventario</h2>
                                    <div class="col-5">
                                        <div class="mb-1 col-12 position-relative">
                                            <div>
                                                <label class="form-label">Sección</label>
                                                <select name="id_section" class="form-select select2" id="select-section-create" data-msg="">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-1 col-12 position-relative">
                                            <div>
                                                <label class="form-label">Categoría</label>
                                                <select name="id_category" class="form-select select2" id="select-category-create" data-msg="">
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-1 col-12 position-relative">
                                            <div>
                                                <label class="form-label">Subcategoría</label>
                                                <select name="id_subcategory" class="form-select select2" id="select-subcategory-create" data-msg="">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-7 d-flex justify-content-end align-items-end">
                                        <table class="table col-8" id="datatable-stock" style="width: 90%;">
                                            <thead>
                                                <tr>
                                                    <th>Almacén</th>
                                                    <th>Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <br>
                                    <div id="container_btn_create_product" class="mb-1 col-12 text-end">
                                        <button id="btn_create_product" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                        <button type="reset" class="btn btn-outline-secondary mt-2 reset" id="btn_cancel_product">
                                            <span>Cancelar</span>
                                        </button>
                                    </div>
                                </form>
                                <!--  -->
                                <form method="POST" enctype="multipart/form-data" id="create_gallery_form" class="mb-2 row" onsubmit="return false">
                                    <h2 class="card-title">Imagenes y tabla</h2>
                                    <div class="col-12">
                                        <div class="text-center mb-2">
                                            <h2>Cargar imágenes</h2>
                                        </div>
                                        <div class="col-md-9 mx-auto" style="height: auto">
                                            <input type="file" multiple name="" id="file_images_product" hidden>
                                            <div id="dropzone" class="rounded d-flex flex-column justify-content-center align-items-center gap-3 p-3" style="border: 2px dashed #7367f0 !important">
                                                <div id="dropzone_guide" class="text-center">
                                                    <h4 class="text-muted my-2">Abrir explorador de archivos</h4>
                                                    <button id="select_image_product" class="border-0" style="width: 54px; height: 54px">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                        </svg>
                                                    </button>
                                                    <h5 class="text-muted my-2">O</h5>
                                                    <h4 class="text-muted my-2">Arrastrar y soltar las imágenes aquí.</h4>
                                                </div>
                                                <div id="dropzone_img" class=" d-flex flex-row justify-content-center flex-wrap gap-3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="card-title"><br><br>
                                            <h2>Carga de tabla</h2><br>
                                            <span class="btn btn-primary" onclick="agregarFila()">Agregar Fila</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card" id="table_product_detail">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-1 col-12 text-end">
                                        <button type="button" class="btn btn-primary" onclick="guardarDetallesProducto()">Guardar Detalles del Producto</button>
                                    </div>

                                    <br>
                                    <input type="hidden" name="id_product">

                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Create -->

                    <!-- Edit -->
                    <div class="row" hidden>
                        <div class="col-12">
                            <div class="card p-3">
                                <h1 class="mb-2 text-center text-muted">FICHA</h1>
                                <form method="POST" enctype="multipart/form-data" id="update_subcategory_form" class="row" onsubmit="return false">
                                    <div class="row">
                                        <div class="mb-4 col-6">
                                            <div class="row">
                                                <div class="mb-1 col-12">
                                                    <label class="form-label">Imágenes</label>
                                                    <input type="file" class="form-control" name="image" autofocus data-msg="" required />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4 col-6">
                                            <div class="row">
                                                <div class="mb-1 col-6">
                                                    <label class="form-label">Código</label>
                                                    <input type="text" name="code" class="form-control" placeholder="Código" autofocus data-msg="" required />
                                                </div>
                                                <div class="mb-1 col-6">
                                                    <label class="form-label">Nombre</label>
                                                    <input type="text" name="name" class="form-control" placeholder="Nombre" autofocus data-msg="" required />
                                                </div>
                                                <div class="mb-3 col-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="" name="description" id="text-description" class="form-control" placeholder="Descripción" rows="3" maxlength="350" style="resize: none;"></textarea>
                                                </div>
                                                <div class="mb-1 col-4 position-relative">
                                                    <div>
                                                        <label class="form-label">Sección</label>
                                                        <select name="id_section" class="form-select select2" id="select-section-update" data-msg="" required>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-1 col-4 position-relative">
                                                    <div>
                                                        <label class="form-label">Categoría</label>
                                                        <select name="id_category" class="form-select select2" id="select-category-update" data-msg="" required>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-1 col-4 position-relative">
                                                    <div>
                                                        <label class="form-label">Subcategoría</label>
                                                        <select name="id_subcategory" class="form-select select2" id="select-subcategory-update" data-msg="" required>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-1 col-6 position-relative">
                                                    <div>
                                                        <label class="form-label">Etiqueta</label>
                                                        <select name="id_label" class="form-select select2" id="select-label-update" data-msg="" required>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-1 col-6">
                                                    <label class="form-label">Precio</label>
                                                    <input type="number" name="price" class="form-control" placeholder="00.00" autofocus data-msg="" required />
                                                </div>
                                                <!-- <div class="mt-2 col-12"><h3 class="text-muted">Stock</h3></div>
                                                    <div class="mb-1 col-6 d-flex align-items-end justify-content-center">
                                                        <div class="col-6">
                                                            <h4>Almacen 1</h4>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Stock</label>
                                                            <input type="number" name="stock" class="form-control" placeholder="00"  autofocus data-msg="" required />
                                                        </div>
                                                </div> -->
                                                <div class="mt-3 col-12">
                                                    <table class="table" id="datatable-products">
                                                        <thead>
                                                            <tr>
                                                                <th>Almacén</th>
                                                                <th>Stock</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                                <input type="hidden" name="id_subcategory">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4 col-12">
                                        <table class="table" id="datatable-products">
                                            <thead>
                                                <tr>
                                                    <th>Características</th>
                                                    <th>Especificaciones</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <br>
                                    <div class="col-12 text-center">
                                        <button id="btn_update_subcategory" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                        <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                                            <span>Cancelar</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Edit -->


                </section>
                <!-- Permissions ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->