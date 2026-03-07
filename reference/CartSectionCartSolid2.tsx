import React from 'react';

const CartSectionCartSolid2: React.FC = () => {
    return (
        <section className="py-12 md:py-24 lg:pb-32 bg-black">
  <div className="container mx-auto px-4">
    <div className="max-w-sm sm:max-w-lg lg:max-w-5xl mx-auto">
      <h3 className="text-4xl text-white font-bold mb-12">Shopping Cart</h3>
      <div className="py-10 border-t border-blueGray-800">
        <div className="flex flex-wrap -mx-4">
          <div className="w-full sm:w-1/3 md:w-1/4 lg:w-2/12 mb-6 sm:mb-0 px-4">
            <img className="block w-38 h-38" src="vendia-assets/images/cart/cart-item2.png" alt="" style={{height: 146, maxWidth: 146}} />
          </div>
          <div className="w-full sm:w-2/3 md:w-9/12 lg:w-10/12 px-4">
            <div className="flex flex-col h-full">
              <div className="flex items-start">
                <div className="sm:flex items-start w-full max-w-sm">
                  <div className="md:flex-shrink-0 mr-12 lg:mr-32 mb-4 sm:mb-0">
                    <span className="block mb-1 text-white font-medium">Bag Tumbler</span>
                    <span className="text-sm font-bold text-gray-400">White</span>
                  </div>
                  <div className="ml-auto mb-4 sm:mb-0">
                    <div className="inline-flex mb-4 pr-1 font-bold text-gray-400 border border-blueGray-800 rounded">
                      <select className="w-12 text-sm font-bold text-center bg-transparent outline-none" name id>
                        <option value={1}>1</option>
                        <option value={2}>2</option>
                        <option value={3}>3</option>
                      </select>
                    </div>
                    <div><a className="inline-block text-sm text-yellow-500 hover:text-white font-bold" href="#">Remove</a></div>
                  </div>
                </div>
                <div className="ml-auto pl-10">
                  <span className="text-sm font-bold text-white">$35.00</span>
                </div>
              </div>
              <div className="mt-auto inline-flex items-center pt-6">
                <svg width={14} height={10} viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fillRule="evenodd" clipRule="evenodd" d="M13.7071 0.292893C14.0976 0.683417 14.0976 1.31658 13.7071 1.70711L5.70711 9.70711C5.31658 10.0976 4.68342 10.0976 4.29289 9.70711L0.292893 5.70711C-0.0976311 5.31658 -0.0976311 4.68342 0.292893 4.29289C0.683417 3.90237 1.31658 3.90237 1.70711 4.29289L5 7.58579L12.2929 0.292893C12.6834 -0.0976311 13.3166 -0.0976311 13.7071 0.292893Z" fill="#F2FF5A" />
                </svg>
                <span className="ml-2 text-sm font-bold text-gray-400">In stock</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="py-10 border-t border-blueGray-800">
        <div className="flex flex-wrap -mx-4">
          <div className="w-full sm:w-1/3 md:w-1/4 lg:w-2/12 mb-6 sm:mb-0 px-4">
            <img className="block w-38 h-38" src="vendia-assets/images/cart/cart-item1.png" alt="" style={{height: 146, maxWidth: 146}} />
          </div>
          <div className="w-full sm:w-2/3 md:w-9/12 lg:w-10/12 px-4">
            <div className="flex flex-col h-full">
              <div className="flex items-start">
                <div className="sm:flex items-start w-full max-w-sm">
                  <div className="md:flex-shrink-0 mr-12 lg:mr-32 mb-4 sm:mb-0">
                    <span className="block mb-1 text-white font-medium">Basic Tee</span>
                    <span className="block text-sm font-bold text-gray-400">Sienna</span>
                    <span className="block text-sm font-bold text-gray-400">Large</span>
                  </div>
                  <div className="ml-auto mb-4 sm:mb-0">
                    <div className="inline-flex mb-4 pr-1 font-bold text-gray-400 border border-blueGray-800 rounded">
                      <select className="w-12 text-sm font-bold text-center bg-transparent outline-none" name id>
                        <option value={1}>1</option>
                        <option value={2}>2</option>
                        <option value={3}>3</option>
                      </select>
                    </div>
                    <div><a className="inline-block text-sm text-yellow-500 hover:text-white font-bold" href="#">Remove</a></div>
                  </div>
                </div>
                <div className="ml-auto pl-10">
                  <span className="text-sm font-bold text-white">$35.00</span>
                </div>
              </div>
              <div className="mt-auto inline-flex items-center pt-6">
                <svg width={14} height={10} viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fillRule="evenodd" clipRule="evenodd" d="M13.7071 0.292893C14.0976 0.683417 14.0976 1.31658 13.7071 1.70711L5.70711 9.70711C5.31658 10.0976 4.68342 10.0976 4.29289 9.70711L0.292893 5.70711C-0.0976311 5.31658 -0.0976311 4.68342 0.292893 4.29289C0.683417 3.90237 1.31658 3.90237 1.70711 4.29289L5 7.58579L12.2929 0.292893C12.6834 -0.0976311 13.3166 -0.0976311 13.7071 0.292893Z" fill="#F2FF5A" />
                </svg>
                <span className="ml-2 text-sm font-bold text-gray-400">In stock</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="py-10 border-t border-blueGray-800">
        <div className="flex flex-wrap -mx-4">
          <div className="w-full sm:w-1/3 md:w-1/4 lg:w-2/12 mb-6 sm:mb-0 px-4">
            <img className="block w-38 h-38" src="vendia-assets/images/cart/cart-item3.png" alt="" style={{height: 146, maxWidth: 146}} />
          </div>
          <div className="w-full sm:w-2/3 md:w-9/12 lg:w-10/12 px-4">
            <div className="flex flex-col h-full">
              <div className="flex items-start">
                <div className="sm:flex items-start w-full max-w-sm">
                  <div className="md:flex-shrink-0 mr-12 lg:mr-32 mb-4 sm:mb-0">
                    <span className="block mb-1 text-white font-medium">Basic Tee</span>
                    <span className="block text-sm font-bold text-gray-400">White</span>
                    <span className="block text-sm font-bold text-gray-400">Large</span>
                  </div>
                  <div className="ml-auto mb-4 sm:mb-0">
                    <div className="inline-flex mb-4 pr-1 font-bold text-gray-400 border border-blueGray-800 rounded">
                      <select className="w-12 text-sm font-bold text-center bg-transparent outline-none" name id>
                        <option value={1}>1</option>
                        <option value={2}>2</option>
                        <option value={3}>3</option>
                      </select>
                    </div>
                    <div><a className="inline-block text-sm text-yellow-500 hover:text-white font-bold" href="#">Remove</a></div>
                  </div>
                </div>
                <div className="ml-auto pl-10">
                  <span className="text-sm font-bold text-white">$35.00</span>
                </div>
              </div>
              <div className="mt-auto inline-flex items-center pt-6">
                <svg width={16} height={16} viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fillRule="evenodd" clipRule="evenodd" d="M8 16C12.4183 16 16 12.4183 16 8C16 3.58172 12.4183 0 8 0C3.58172 0 0 3.58172 0 8C0 12.4183 3.58172 16 8 16ZM9 4C9 3.44772 8.55229 3 8 3C7.44772 3 7 3.44772 7 4V8C7 8.26522 7.10536 8.51957 7.29289 8.70711L10.1213 11.5355C10.5118 11.9261 11.145 11.9261 11.5355 11.5355C11.9261 11.145 11.9261 10.5118 11.5355 10.1213L9 7.58579V4Z" fill="#84878A" />
                </svg>
                <span className="ml-2 text-sm font-bold text-gray-400">Ships in 3-4 weeks</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="pt-10 border-t border-blueGray-800">
        <div className="lg:max-w-md lg:ml-auto">
          <div className="mb-4 bg-blueGray-900">
            <div className="pt-7 pb-8 px-8">
              <div className="mb-4 pb-4 border-b border-blueGray-800">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-bold text-gray-400">Subtotal</span>
                  <span className="text-sm font-bold text-white">$99.00</span>
                </div>
              </div>
              <div className="mb-4 pb-4 border-b border-blueGray-800">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-bold text-gray-400">Shipping</span>
                  <span className="text-sm font-bold text-white">$5.00</span>
                </div>
              </div>
              <div className="mb-4 pb-4 border-b border-blueGray-800">
                <div className="flex items-center justify-between">
                  <span className="text-sm font-bold text-gray-400">Tax</span>
                  <span className="text-sm font-bold text-white">$8.32</span>
                </div>
              </div>
              <div className="flex items-center justify-between">
                <span className="font-bold text-white">Order total</span>
                <span className="text-sm font-bold text-white">$112.32</span>
              </div>
            </div>
            <button className="block w-full px-6 py-3 text-center font-bold text-black bg-yellow-500 hover:bg-yellow-600 transition duration-200">Checkout</button>
          </div>
          <p className="text-center mb-0">
            <span className="text-sm font-bold text-gray-400">or</span>
            <a className="inline-flex items-center ml-2 text-sm font-bold text-yellow-500 hover:text-yellow-600" href="#">
              <span className="mr-2">Continue Shopping</span>
              <svg width={14} height={12} viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.33333 1.33331L13 5.99998M13 5.99998L8.33333 10.6666M13 5.99998L1 5.99998" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</section>


    );
};

export default CartSectionCartSolid2;