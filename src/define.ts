define(
    [
        "dojo",
        "dojo/_base/declare",
        "ebg/core/gamegui",
        "ebg/counter",
        "ebg/zone"
    ],
    function (dojo: any, declare: any) {
        return declare("bgagame.innovation", ebg.core.gamegui, new Innovation());
    }
);