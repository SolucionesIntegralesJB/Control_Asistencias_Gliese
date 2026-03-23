// --
$(document).ready(function () {
  // --
  feather.replace();

  // Track which tabs have been loaded
  const loadedTabs = {
    "company-info": false,
    "general-config": false,
    security: false,
    email: false,
    whatsapp: false,
    "ai-config": false,
    menus: false,
    messages: false,
    faq: false,
  };

  // Module scripts mapping
  const tabScripts = {
    "company-info": "company_info",
    "general-config": "general_config",
    security: "security",
    email: "email",
    whatsapp: "whatsapp",
    "ai-config": "ai_config",
    menus: "menus",
    messages: "messages",
    faq: "faq",
  };

  // Restore last active tab from localStorage
  const lastActiveTab = localStorage.getItem('bot_config_active_tab') || 'company-info';

  // Activate the last active tab
  if (lastActiveTab && lastActiveTab !== 'company-info') {
    // Remove active class from default tab
    $('#company-tab').removeClass('active');
    $('#company-info').removeClass('active show');

    // Activate saved tab
    $(`a[href="#${lastActiveTab}"]`).addClass('active');
    $(`#${lastActiveTab}`).addClass('active show');
  }

  // Load the active tab content
  loadTabContent(lastActiveTab);

  // Tab click event - lazy loading and save active tab
  $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
    const targetTab = $(e.target).attr("href").substring(1); // Remove #

    // Save active tab to localStorage
    localStorage.setItem('bot_config_active_tab', targetTab);

    if (!loadedTabs[targetTab]) {
      loadTabContent(targetTab);
    }
  });

  // --
  function loadTabContent(tabId) {
    if (loadedTabs[tabId]) {
      return; // Already loaded
    }

    const contentDiv = $(`#${tabId}-content`);
    const scriptName = tabScripts[tabId];

    // Show loading spinner
    contentDiv.html(`
            <div class="d-flex justify-content-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);

    // Load partial HTML
    $.ajax({
      url: BASE_URL + "Bot_Configuration/load_partial",
      method: "POST",
      data: { partial: scriptName },
      success: function (response) {
        contentDiv.html(response);
        feather.replace();

        // Dynamically load tab-specific JavaScript
        loadTabScript(scriptName, tabId);
      },
      error: function () {
        contentDiv.html(`
                    <div class="alert alert-danger" role="alert">
                        <i data-feather="alert-circle"></i> Error al cargar el contenido.
                    </div>
                `);
        feather.replace();
      },
    });

    loadedTabs[tabId] = true;
  }

  // --
  function loadTabScript(scriptName, tabId) {
    const scriptPath =
      BASE_URL + `application/views/bot_configuration/js/${scriptName}.js`;

    // Check if script is already loaded
    if ($(`script[src="${scriptPath}"]`).length > 0) {
      return;
    }

    // Load script dynamically
    $.getScript(scriptPath)
      .done(function () {
        console.log(`[Bot Configuration] ${scriptName}.js loaded successfully`);

        // Initialize tab-specific functions if they exist
        if (
          window[`init_${scriptName}`] &&
          typeof window[`init_${scriptName}`] === "function"
        ) {
          window[`init_${scriptName}`]();
        }
      })
      .fail(function () {
        console.error(`[Bot Configuration] Failed to load ${scriptName}.js`);
      });
  }
});
