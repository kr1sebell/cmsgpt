export default {
    async fetch(req: Request): Promise<Response> {
        const url = new URL(req.url);

        // health-check без секрета
        if (req.method === "GET" && url.searchParams.get("health") === "1") {
            return new Response(JSON.stringify({ ok: true, service: "deno-playground" }), {
                headers: { "Content-Type": "application/json" },
            });
        }

        // проверка секрета
        const token = req.headers.get("X-Relay-Token") || "";
        if (!token || token !== (Deno.env.get("RELAY_SHARED_TOKEN") || "")) {
            return new Response(JSON.stringify({ error: "forbidden" }), {
                status: 403,
                headers: { "Content-Type": "application/json" },
            });
        }

        const body = await req.json().catch(() => ({}));

        let targetUrl = "https://api.openai.com/v1/chat/completions";
        let payload: any = {
            model: body.model ?? "gpt-4.1-mini",
            messages: Array.isArray(body.messages) ? body.messages : [],
            temperature: body.temperature ?? 0.2,
            max_tokens: body.max_tokens ?? 400,
            stream: false,
        };

        // если запрос на картинки (или другой спец эндпоинт) → шлём как есть
        if (url.pathname === "/images/generations" || url.pathname === "/embeddings" || url.pathname.startsWith("/audio/")) {
            targetUrl = "https://api.openai.com/v1" + url.pathname;
            payload = body; // ничего не меняем
        }

        const r = await fetch(targetUrl, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${Deno.env.get("OPENAI_API_KEY")}`,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(payload),
        });

        const txt = await r.text();
        return new Response(txt, {
            status: r.status,
            headers: { "Content-Type": "application/json" },
        });
    },
};