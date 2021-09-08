import React, {useEffect, useRef} from "react";
// import Choices from "choices.js";
// import "choices.js/public/assets/styles/base.min.css";
// import "choices.js/public/assets/styles/choices.min.css";

export default function () {

    const elem = useRef(null);
    const elem2 = useRef(null);


    useEffect(() => {
        // console.log("mounted", elem)
        //
        // new Choices(elem.current);
        // new Choices(elem2.current);

    }, [])

    return (
        <div>
            <h6>Single</h6>
            <select ref={elem} name="example">
                <option value="a">a option.a</option>
                <option value="b">b option.b</option>
                <option value="c">c option.c</option>
                <option value="d">d option.d</option>
            </select>

            <h6>Multiple</h6>
            <select multiple={true} ref={elem2} name="example">
                <option value="a">a option.a</option>
                <option value="b">b option.b</option>
                <option value="c">c option.c</option>
                <option value="d">d option.d</option>
            </select>
        </div>
    )
}