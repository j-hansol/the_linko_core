let Utils = {
    /**
     * 문서의 CSRF 토큰을 추출하여 리턴한다.
     * @returns {*|string}
     */
    getCSRF: () => {
        let obj = document.querySelector('meta[name=csrf-token]');
        return obj.content ?? '';
    },

    /**
     * 전달된 문자열이 비었넌지 여부를 판단하여 리턴한다.
     * @param str
     * @returns {boolean}
     */
    isEmpty: (str) => {
        let expr = /^[\s\t]*$/;
        if (str == null) return true;
        if ('string' != typeof str) return true;
        if (str === '' || expr.exec(str)) return true;

        return false;
    },

    /**
     * 메뉴 활성화 설정을 한다.
     * @param group_wrapper_class
     * @param active_group
     */
    setActiveMenu: (group_wrapper_class, active_group) => {
        let groups = document.getElementsByClassName(group_wrapper_class);
        for (let i = 0; i < groups.length; i++) groups[i].classList.remove('active');
        active_group.classList.add('active');
    },

    /**
     * 일괄처리용 모든 체크박스를 체크 상태로 변경한다.
     * @param item_class
     * @param event
     */
    setAllBatchBox: function(event) {
        let target = event.target;
        let status = target.checked;
        let item_class = target.getAttribute('data-item');
        let boxes = document.getElementsByClassName(item_class);
        for(let i = 0 ; i < boxes.length; i++) {
            boxes[i].checked = status;
        }
    },

    /**
     * 상태를 체크하여 전체 선택 여부를 설정한다.
     * @param item_class
     * @param update_id
     */
    updateOperationStatus: function(item_class, update_id) {
        let items = document.getElementsByClassName(item_class);
        let status_object = document.getElementById(update_id)
        let length = items.length;
        let checked_items = 0;

        if(!status_object) return;

        for(let i = 0 ; i < length; i++) {
            checked_items += items[i].checked ? 1 : 0;
        }
        status_object.checked = checked_items === length;
    },

    /**
     * 배치 처리 이벤트 헨들러를 등록한다.
     * @param item_class
     * @param update_id
     */
    setOperationHandler: function(item_class, update_id) {
        let items = document.getElementsByClassName(item_class);
        if (items.length === 0) return;

        for (let i = 0; i < items.length; i++)
            items[i].addEventListener('click', () => Utils.updateOperationStatus(item_class, update_id));
    },

    /**
     * 특정 일괄처리 체크박스 이벤트를 설정한다.
     * @param update_id
     */
    setBachOperation: function(update_id) {
        let obj = document.getElementById(update_id)
        let action = obj.getAttribute('data-action');
        if(!action || action !== 'operation') return;

        let item_class = obj.getAttribute('data-item');
        obj.addEventListener('click', Utils.setAllBatchBox);
        this.setOperationHandler(item_class, update_id);
    },

    /**
     * 모든 일괄처리 체크박스 이벤트를 설정한다.
     */
    initBachOperation: function() {
        let operation_boxes = document.querySelectorAll('input[data-action=operation]');
        for(let i = 0; i < operation_boxes.length ; i++) {
            let id = operation_boxes[i].getAttribute('id');
            this.setBachOperation(id);
        }
    },

    /**
     * 팝업창을 오픈한다.
     * @param url
     * @param target
     * @param option
     */
    openPopup: function(url, target, option) {
        if(!target) target = 'window';
        if(!option) option = 'width=600,height=800';
        if(!url) return;

        window.open(url, target, option);
    },

    /**
     * 지정 객체에 사용자 정의 이벤트를 전달한다.
     * @param obj
     * @param name
     * @param params
     */
    triggerCustomEvent: function(obj, name, params) {
        let ev = null;
        if(!obj) return;
        if(params) ev = new CustomEvent(name, {detail : params});
        else ev = new CustomEvent(name);
        obj.dispatchEvent(ev);
    }
};

export default Utils;
