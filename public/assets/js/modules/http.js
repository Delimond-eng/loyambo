/***
 * Fait passer les requêtes HTTP en GET ou en POST,
 * si data est défini c'est la requête POST qui sera lancée,
 * autrement c'est la requête GET qui sera lancée
 * @param {String} [url=null]
 * @param {Object} form
 * @returns {data, status} data: http response if status equal 200 or 201
 */
export async function post(url, form) {
    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                accept: "application/json",
            },
            body: form,
        });
        const data = await response.json();
        return { data, status: response.status };
    } catch (error) {
        console.error("Error:", error);
        throw new Error("La requête a échoué");
    }
}

/***
 * Fait passer les requêtes HTTP en GET ou en POST,
 * si data est défini c'est la requête POST qui sera lancée,
 * autrement c'est la requête GET qui sera lancée
 * @param {String} [url=null]
 * @param {Object} form
 * @returns {data, status} data: http response if status equal 200 or 201
 */
export async function postJson(url, form) {
    try {
        var csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        const response = await fetch(url, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json",
                accept: "application/json",
            },
            body: JSON.stringify(form),
        });
        const data = await response.json();
        return { data, status: response.status };
    } catch (error) {
        console.error("Error:", error);
        throw new Error("La requête a échoué");
    }
}

/**
 * Fait une requete en GET
 * @param {*} url
 * @returns {data, status} data: http response if status equal to 200 or 201
 */
export async function get(url) {
    const response = await fetch(url, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            accept: "application/json",
        },
    });
    const data = await response.json();
    return { data, status: response.status };
}
