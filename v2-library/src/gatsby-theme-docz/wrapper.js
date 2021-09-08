import React from "react";
import { Helmet, HelmetProvider } from "react-helmet-async";
import { withPrefix } from "gatsby";

export default function ({ children }) {
    return (
        <HelmetProvider>
            <Helmet>
                <title>Hello World</title>
                <script src={withPrefix('/bundle/main.min.js')} type="text/javascript" />
            </Helmet>
            <div className="wrapper-x">{ children }</div>
        </HelmetProvider>
    )
}