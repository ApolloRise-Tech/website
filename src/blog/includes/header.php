<header class="header _anim-items _anim-no-hide">
    <div class="header__wrapper">
        <a href="/">
            <img class="logo" src="/blog/assets/logo.svg" alt="ApolloRise Tech" width="123" height="32">
        </a>
        <nav class="header__navigation">
            <ul class="header__listLink">
                <li class="header__link"><a href="/#about" class="menu_link linkWithUnderline">About</a></li>
                <li class="header__link"><a href="/#whatWeDo" class="menu_link linkWithUnderline">Services</a></li>
                <li class="header__link"><a href="/portfolio" class="menu_link linkWithUnderline">Our Work</a></li>
                <li class="header__link"><a href="/founders" class="menu_link linkWithUnderline">Founders</a></li>
                <li class="header__link"><a href="/blog/" class="menu_link linkWithUnderline">Blog</a></li>
                <li class="header__link"><a href="/career" class="menu_link linkWithUnderline">Careers</a></li>
                <li class="header__link header__connect">
                    <a href="/#contactUs" class="menu_link">Connect with us</a>
                </li>
            </ul>
        </nav>
        <button class="header__burger">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                <g clip-path="url(#clip_burger)">
                    <path d="M3.75 12.5H20.25" stroke="black" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"/>
                    <path d="M3.75 6.5H20.25" stroke="black" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"/>
                    <path d="M3.75 18.5H20.25" stroke="black" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"/>
                </g>
                <defs><clipPath id="clip_burger"><rect width="24" height="24" fill="white" transform="translate(0 0.5)"/></clipPath></defs>
            </svg>
        </button>
    </div>
    <div class="header__mobile_container">
        <div class="header__mobile_wrapper">
            <img class="logo" src="/blog/assets/logo.svg" alt="ApolloRise Tech" width="123" height="32">
            <div class="header__mobile_link header__mobile_connect">
                <a href="/#contactUs">Connect with us</a>
            </div>
            <button class="header__close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <g clip-path="url(#clip_close)">
                        <path d="M18.75 5.25L5.25 18.75" stroke="#23221F" stroke-width="1.5" stroke-linecap="square"/>
                        <path d="M18.75 18.75L5.25 5.25" stroke="#23221F" stroke-width="1.5" stroke-linecap="square"/>
                    </g>
                    <defs><clipPath id="clip_close"><rect width="24" height="24" fill="white"/></clipPath></defs>
                </svg>
            </button>
        </div>
        <nav class="header__mobile_navigation">
            <ul class="header__mobile_listLink">
                <li class="header__mobile_link"><a href="/#about" class="linkWithUnderline">About</a></li>
                <li class="header__mobile_link"><a href="/#whatWeDo" class="linkWithUnderline">Services</a></li>
                <li class="header__mobile_link"><a href="/portfolio" class="linkWithUnderline">Our Work</a></li>
                <li class="header__mobile_link"><a href="/founders" class="linkWithUnderline">Founders</a></li>
                <li class="header__mobile_link"><a href="/blog/" class="linkWithUnderline">Blog</a></li>
                <li class="header__mobile_link"><a href="/career" class="linkWithUnderline">Careers</a></li>
            </ul>
        </nav>
        <div class="header__mobile_link header__mobile_connect small-mobile">
            <a href="/#contactUs" class="menu_link">Connect with us</a>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('.header');
    const burger = document.querySelector('.header__burger');
    const close = document.querySelector('.header__close');
    const mobileContainer = document.querySelector('.header__mobile_container');

    // Make header visible (site CSS starts it at opacity:0, _active shows it)
    if (header) header.classList.add('_active');

    if (burger && mobileContainer) {
        burger.addEventListener('click', () => mobileContainer.classList.add('active'));
    }
    if (close && mobileContainer) {
        close.addEventListener('click', () => mobileContainer.classList.remove('active'));
    }

    window.addEventListener('scroll', function() {
        if (!header) return;
        if (window.pageYOffset > 55) {
            header.classList.add('header-scroll');
        } else {
            header.classList.remove('header-scroll');
        }
    });
});
</script>
