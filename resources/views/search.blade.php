<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Search</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            max-width: 900px;
        }

        .search_section {
            margin-bottom: 20px;
        }

        #search_value {
            width: 400px;
            padding: 10px;
        }

        button {
            padding: 10px 15px;
            cursor: pointer;
        }

        .result-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .result-item a {
            font-size: 18px;
            color: #1a0dab;
            text-decoration: none;
        }

        .result-item p {
            margin: 8px 0 0;
            color: #555;
        }

        .position {
            font-weight: bold;
            margin-bottom: 5px;
        }

        #download_btn {
            display: none;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <h1>Google Organic Search</h1>

    <div class="search_section">
        <input
            type="text"
            id="search_value"
            placeholder="Enter keyword"
        >

        <button id="search_button">
            Search
        </button>
    </div>

    <div id="download_btn">
        <button id="download_json">
            Save as JSON
        </button>
    </div>

    <div id="results"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function () {

            let currentResults = [];

            $("#search_button").on("click", function () {

                const keyword = $("#search_value").val().trim();

                if (keyword === "") {
                    alert("Please enter a keyword phrase.");
                    return;
                }

                $("#results").html("<p>Searching Google...</p>");
                $("#download_btn").hide();

                $.ajax({
                    url: "/search",
                    method: "POST",

                    data: {
                        query: keyword,
                        _token: "{{ csrf_token() }}"
                    },

                    success: function (response) {

                        currentResults = response.results || [];

                        if (currentResults.length === 0) {
                            $("#results").html(
                                "<p>No organic results found.</p>"
                            );
                            return;
                        }

                        let html = "";

                        currentResults.forEach(function (item) {

                            html += `
                                <div class="result-item">

                                    <div class="position">
                                        ${item.position}.
                                    </div>

                                    <a
                                        href="${item.link}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        ${escapeHtml(item.title)}
                                    </a>

                                    <p>
                                        ${escapeHtml(item.snippet)}
                                    </p>

                                </div>
                            `;
                        });

                        $("#results").html(html);

                        $("#download_btn").show();
                    },

                    error: function (xhr) {

                        const message =
                            xhr.responseJSON?.message ||
                            "Search failed.";

                        $("#results").html(
                            `<p>${escapeHtml(message)}</p>`
                        );

                        $("#download_btn").hide();
                    }
                });
            });


            // JSON download
            $("#download_json").on("click", function () {

                if (currentResults.length === 0) {
                    return;
                }

                const json = JSON.stringify(
                    currentResults,
                    null,
                    2
                );

                const blob = new Blob(
                    [json],
                    { type: "application/json" }
                );

                const url = URL.createObjectURL(blob);

                const link = document.createElement("a");

                link.href = url;
                link.download = "google_results.json";

                document.body.appendChild(link);

                link.click();

                document.body.removeChild(link);

                URL.revokeObjectURL(url);
            });

            function escapeHtml(value) {

                return String(value)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

        });
    </script>

</body>
</html>