<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Test d'Upload de Fichier</h3>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Sélectionnez un fichier</label>
                                <input class="form-control" type="file" id="file" name="file" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Télécharger</button>
                            </div>
                        </form>
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: '/test-upload',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#result').html(`
                                <div class="alert alert-success">
                                    <strong>Succès!</strong> ${response.message}
                                    <div>Chemin: ${response.path}</div>
                                    <div>URL: <a href="${response.url}" target="_blank">${response.url}</a></div>
                                </div>
                            `);
                        } else {
                            $('#result').html(`
                                <div class="alert alert-danger">
                                    <strong>Erreur!</strong> ${response.message}
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'Une erreur est survenue lors de l\'upload du fichier.';
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            console.error('Erreur lors de l\'analyse de la réponse:', e);
                        }
                        
                        $('#result').html(`
                            <div class="alert alert-danger">
                                <strong>Erreur!</strong> ${errorMessage}
                            </div>
                        `);
                    }
                });
            });
        });
    </script>
</body>
</html>
