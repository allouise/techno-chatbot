document.addEventListener("DOMContentLoaded", function () {

    const button = document.getElementById("techno-crawl-page");

    if (!button) return;

    button.addEventListener("click", async function () {

        button.disabled = true;
        button.innerText = "Crawling...";

        const formData = new FormData();
        formData.append("action", "techno_chatbot_crawl_page");
        formData.append("post_id", technoaidb.post_id);
        formData.append("nonce", technoaidb.nonce);

        try {
            const response = await fetch(technoaidb.ajax_url, {
                method: "POST",
                credentials: "same-origin",
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                button.innerText = "Success!";

                setTimeout(() => {
                    window.location.reload();
                }, 800);
            } else {
                alert("Error: " + data.data);

                button.disabled = false;
                button.innerText = "Crawl This Page";
            }

        } catch (error) {
            alert("Request failed");
        }

        button.disabled = false;
        button.innerText = "Crawl This Page";
    });
});