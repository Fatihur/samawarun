import $ from 'jquery';
window.$ = window.jQuery = $; // Ensure global availability for older plugins
import DataTable from 'datatables.net-dt';
import 'datatables.net-responsive-dt';

// Import CSS
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';

// TinyMCE - self-hosted via npm (no API key needed)
import 'tinymce';
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/models/dom';

// TinyMCE plugins
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/autolink';

// TinyMCE skins
import 'tinymce/skins/ui/oxide/skin.min.css';
import 'tinymce/skins/content/default/content.min.css';
import 'tinymce/skins/content/default/content.css';

// TinyMCE Initialization
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelectorAll('textarea.richtext').length > 0) {
        tinymce.init({
            selector: 'textarea.richtext',
            height: 300,
            menubar: false,
            branding: false,
            promotion: false,
            plugins: 'lists link autolink',
            toolbar: 'blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | link | removeformat',
            block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.6; }',
            skin: false,
            content_css: false,
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    }
});

// DataTables Initialization
$(document).ready(function() {
    if ($('.datatable').length > 0) {
        $('.datatable').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                return;
            }

            $(this).DataTable({
                responsive: true,
                order: [], // Disable initial sorting, respect backend order
                language: {
                    emptyTable: "Tidak ada data yang tersedia pada tabel ini",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
                    lengthMenu: "Tampilkan _MENU_ entri",
                    search: "Cari:",
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate > .pagination').addClass('flex items-center gap-1');
                }
            });
        });
    }
});
