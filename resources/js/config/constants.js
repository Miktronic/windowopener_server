const constants = {
    PUBLIC_URL: process.env.MIX_HOST_URL,
    HOST_URL: process.env.MIX_HOST_URL + "/api",

    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
};

export default constants;
