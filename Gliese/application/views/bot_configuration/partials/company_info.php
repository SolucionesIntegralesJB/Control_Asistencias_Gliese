<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Información de la Empresa</h4>
        <p class="text-muted">Configure los datos de su empresa que serán utilizados por el bot.</p>
    </div>
</div>

<form id="form-company-info">

    <!-- Acordeón 1: Información Básica de la Empresa -->
    <div class="accordion accordion-margin" id="accordionBasicInfo">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingBasicInfo">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasicInfo" aria-expanded="true" aria-controls="collapseBasicInfo">
                    <i data-feather="briefcase" class="me-2"></i> Información Básica de la Empresa
                </button>
            </h2>
            <div id="collapseBasicInfo" class="accordion-collapse collapse show" aria-labelledby="headingBasicInfo" data-bs-parent="#accordionBasicInfo">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="company_name">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_name" name="company_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="company_phone">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" placeholder="+51 987 654 321" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="company_email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="company_email" name="company_email" placeholder="contacto@empresa.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="company_website">Sitio Web</label>
                            <input type="text" class="form-control" id="company_website" name="company_website" placeholder="www.empresa.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acordeón 2: Ubicación -->
    <div class="accordion accordion-margin" id="accordionLocation">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingLocation">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLocation" aria-expanded="false" aria-controls="collapseLocation">
                    <i data-feather="map-pin" class="me-2"></i> Ubicación
                </button>
            </h2>
            <div id="collapseLocation" class="accordion-collapse collapse" aria-labelledby="headingLocation" data-bs-parent="#accordionLocation">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="company_address">Dirección Completa</label>
                            <input type="text" class="form-control" id="company_address" name="company_address" placeholder="Av. Principal 123, Piso 2">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="company_city">Ciudad</label>
                            <input type="text" class="form-control" id="company_city" name="company_city" placeholder="Lima">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="company_region">Región/Departamento</label>
                            <input type="text" class="form-control" id="company_region" name="company_region" placeholder="Lima">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="company_country">País</label>
                            <input type="text" class="form-control" id="company_country" name="company_country" placeholder="Perú">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acordeón 3: Redes Sociales -->
    <div class="accordion accordion-margin" id="accordionSocial">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSocial">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSocial" aria-expanded="false" aria-controls="collapseSocial">
                    <i data-feather="share-2" class="me-2"></i> Redes Sociales
                </button>
            </h2>
            <div id="collapseSocial" class="accordion-collapse collapse" aria-labelledby="headingSocial" data-bs-parent="#accordionSocial">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="social_facebook">Facebook URL</label>
                            <input type="url" class="form-control" id="social_facebook" name="social_facebook" placeholder="https://www.facebook.com/empresa">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="social_instagram">Instagram URL</label>
                            <input type="url" class="form-control" id="social_instagram" name="social_instagram" placeholder="https://www.instagram.com/empresa">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acordeón 4: Horarios de Atención -->
    <div class="accordion accordion-margin" id="accordionSchedule">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingSchedule">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSchedule" aria-expanded="false" aria-controls="collapseSchedule">
                    <i data-feather="clock" class="me-2"></i> Horarios de Atención
                </button>
            </h2>
            <div id="collapseSchedule" class="accordion-collapse collapse" aria-labelledby="headingSchedule" data-bs-parent="#accordionSchedule">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="schedule_weekdays">Lunes a Viernes</label>
                            <input type="text" class="form-control" id="schedule_weekdays" name="schedule_weekdays" placeholder="8:00 AM - 6:00 PM">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="schedule_saturday">Sábados</label>
                            <input type="text" class="form-control" id="schedule_saturday" name="schedule_saturday" placeholder="8:00 AM - 2:00 PM">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="schedule_sunday">Domingos</label>
                            <input type="text" class="form-control" id="schedule_sunday" name="schedule_sunday" placeholder="Solo emergencias">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="schedule_emergency">Atención de Emergencias</label>
                            <input type="text" class="form-control" id="schedule_emergency" name="schedule_emergency" placeholder="24/7">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acordeón 5: Bot y Enlaces de la Empresa -->
    <div class="accordion accordion-margin" id="accordionBot">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingBot">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBot" aria-expanded="false" aria-controls="collapseBot">
                    <i data-feather="link" class="me-2"></i> Bot y Enlaces de la Empresa
                </button>
            </h2>
            <div id="collapseBot" class="accordion-collapse collapse" aria-labelledby="headingBot" data-bs-parent="#accordionBot">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="assistant_name">Nombre del Asistente Virtual</label>
                            <input type="text" class="form-control" id="assistant_name" name="assistant_name" placeholder="Carla">
                            <small class="text-muted">Nombre que usará el bot al presentarse</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="google_sheet_id">ID de Google Sheet</label>
                            <input type="text" class="form-control" id="google_sheet_id" name="google_sheet_id" placeholder="1EFpk67bT6H7HuiwnpKnAKJtLTd2tk8YML_iHdE3VKqk">
                            <small class="text-muted">ID de la hoja de cálculo de Google</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="google_form_url">URL del Formulario de Google</label>
                            <input type="url" class="form-control" id="google_form_url" name="google_form_url" placeholder="https://docs.google.com/forms/...">
                            <small class="text-muted">Link del formulario de registro</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="whatsapp_group_link">Link del Grupo de WhatsApp</label>
                            <input type="url" class="form-control" id="whatsapp_group_link" name="whatsapp_group_link" placeholder="https://chat.whatsapp.com/...">
                            <small class="text-muted">Link de invitación al grupo</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="media_storage_path">Ruta de Almacenamiento de Medios</label>
                            <input type="text" class="form-control" id="media_storage_path" name="media_storage_path" placeholder="./uploads">
                            <small class="text-muted">Carpeta donde se guardan archivos multimedia</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary" id="btn-save-company">
                <i data-feather="save"></i> Guardar Cambios
            </button>
        </div>
    </div>
</form>
