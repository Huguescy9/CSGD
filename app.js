// Initialisation du document
document.addEventListener('DOMContentLoaded', async function() {
    // Vérification de l'authentification de l'utilisateur
    try {
        const authResponse = await fetch('check_auth.php');
        const authData = await authResponse.json();
        
        if (!authData.authenticated) {
            window.location.href = 'login.php';
            return;
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'login.php';
        return;
    }

    // Initialisation des sélecteurs de date avec datepicker
    $('.date-filter').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        language: 'fr',
        todayHighlight: true
    });

    // Extension de DataTables pour le filtrage par date personnalisé
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        let tableId = $(settings.nTable).attr('id');
        let dateColumnIndex = tableId === 'employeesTable' ? 7 : 4; // Index de la colonne date
        
        let startDate = $(`#${tableId === 'employeesTable' ? 'employeeStartDate' : 'equipmentStartDate'}`).datepicker('getDate');
        let endDate = $(`#${tableId === 'employeesTable' ? 'employeeEndDate' : 'equipmentEndDate'}`).datepicker('getDate');
        let cellDate = moment(data[dateColumnIndex], 'DD/MM/YYYY').toDate();

        // Logique de filtrage des dates
        if (!startDate && !endDate) return true;
        if (!cellDate) return false;
        
        if (startDate && !endDate) return cellDate >= startDate;
        if (!startDate && endDate) return cellDate <= endDate;
        return cellDate >= startDate && cellDate <= endDate;
    });

    // Configuration commune pour les tableaux DataTables
    const commonConfig = {
        responsive: true,
        processing: true,
        scrollX: true,
        // Configuration du nombre d'entrées par page avec option "Tout"
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Tout']
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
            search: "Rechercher:",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Aucune donnée disponible",
            infoFiltered: "(filtré parmi _MAX_ entrées)"
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"fl>rt<"d-flex justify-content-between align-items-center"ip>',
        order: [[1, 'asc']]
    };

    // Initialisation du tableau des employés
    const employeesTable = $('#employeesTable').DataTable({
        ...commonConfig,
        ajax: {
            url: 'get_data.php?type=employees',
            dataSrc: function(json) {
                return json.employees.map(emp => {
                    emp.created_at = moment(emp.created_at).format('DD/MM/YYYY');
                    return emp;
                });
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'select-checkbox text-center',
                render: () => '<input type="checkbox" class="form-check-input row-checkbox">'
            },
            { data: 'nom' },
            { data: 'prenom' },
            { data: 'matricule' },
            { data: 'email' },
            { data: 'service' },
            { data: 'metier' },
            { data: 'created_at', title: "Date d'embauche" }
        ]
    });

    // Initialisation du tableau du matériel
    const equipmentTable = $('#equipmentTable').DataTable({
        ...commonConfig,
        ajax: {
            url: 'get_data.php?type=equipment',
            dataSrc: function(json) {
                return json.equipment.map(eq => {
                    eq.date_achat = moment(eq.date_achat).format('DD/MM/YYYY');
                    return eq;
                });
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'select-checkbox text-center',
                render: () => '<input type="checkbox" class="form-check-input row-checkbox">'
            },
            { data: 'matricule' },
            { data: 'type' },
            { data: 'serie' },
            { data: 'date_achat', title: "Date d'achat" },
            { data: 'reference' }
        ]
    });

    // Gestion des changements de filtres de date
    $('.date-filter').change(function() {
        let tableId = $(this).data('table');
        $(`#${tableId}`).DataTable().draw();
    });

    // Gestion de la sélection de toutes les cases à cocher
    $('.select-all').on('change', function() {
        const tableId = $(this).data('table');
        const table = $(`#${tableId}Table`).DataTable();
        const checked = $(this).prop('checked');
        
        table.rows({page: 'current'}).nodes().each(function(node) {
            $(node).find('.row-checkbox').prop('checked', checked);
        });
    });

    // Gestion des cases à cocher individuelles
    $(document).on('change', '.row-checkbox', function() {
        const table = $(this).closest('table');
        const allCheckbox = table.find('.select-all');
        const visibleRows = table.find('tbody tr:visible .row-checkbox').length;
        const checkedVisibleRows = table.find('tbody tr:visible .row-checkbox:checked').length;
        
        allCheckbox.prop('checked', visibleRows === checkedVisibleRows);
    });

    // Génération des PDF
    function getSelectedData(tableId) {
        const table = $(`#${tableId}`).DataTable();
        const selectedData = [];
        
        table.rows({page: 'current'}).nodes().each(function(node) {
            if($(node).find('.row-checkbox').prop('checked')) {
                selectedData.push(table.row(node).data());
            }
        });
        
        return selectedData;
    }

    $('#generateWelcome').on('click', async function() {
        const selectedData = getSelectedData('employeesTable');
        if(selectedData.length === 0) {
            alert('Veuillez sélectionner au moins un employé.');
            return;
        }
        await generatePDF('welcome', selectedData);
    });

    $('#generateEquipment').on('click', async function() {
        const selectedData = getSelectedData('equipmentTable');
        if(selectedData.length === 0) {
            alert('Veuillez sélectionner au moins un matériel.');
            return;
        }
        await generatePDF('equipment', selectedData);
    });

    async function generatePDF(type, data) {
        try {
            const response = await fetch('generate_pdf.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ type, data })
            });

            if(!response.ok) throw new Error('Erreur réseau');
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${type}_${new Date().toISOString().slice(0,10)}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch(error) {
            console.error('Erreur génération PDF:', error);
            alert('Erreur lors de la génération du PDF');
        }
    }

    // Gestion des soumissions de formulaires
    ['employeeForm', 'equipmentForm'].forEach(formId => {
        document.getElementById(formId).addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: new FormData(this)
                });
                const data = await response.json();
                
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                }
            } catch(error) {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'upload');
            }
        });
    });

    // Fonctions d'exportation CSV
    function downloadCSV(data, filename) {
        const csvContent = data.map(row => 
            Object.values(row).map(val => 
                `"${val?.toString().replace(/"/g, '""') || ''}"`)
            .join(',')
        );
        
        const headers = Object.keys(data[0]).join(',');
        const csv = [headers, ...csvContent].join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (navigator.msSaveBlob) {
            navigator.msSaveBlob(blob, filename);
        } else {
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    $('#exportEmployeesCSV').on('click', function() {
        const selectedData = getSelectedData('employeesTable');
        if(selectedData.length === 0) {
            alert('Veuillez sélectionner au moins un employé.');
            return;
        }
        downloadCSV(selectedData, `employes_${new Date().toISOString().slice(0,10)}.csv`);
    });

    $('#exportEquipmentCSV').on('click', function() {
        const selectedData = getSelectedData('equipmentTable');
        if(selectedData.length === 0) {
            alert('Veuillez sélectionner au moins un matériel.');
            return;
        }
        downloadCSV(selectedData, `materiel_${new Date().toISOString().slice(0,10)}.csv`);
    });

    // Gestion du redimensionnement des tableaux
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });
});
